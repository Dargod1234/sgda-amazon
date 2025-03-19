<?php

namespace App\Http\Controllers;


use App\Models\Archivo;
use App\Models\ArchivoPermiso;
use App\Models\Carpeta;
use App\Models\GoogleToken;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Http\Controllers\GoogleAuthController;
use App\Events\ArchivoCreado;
use Illuminate\Support\Facades\Storage;

use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\OnBehalfOfContext;
use Microsoft\Graph\Generated\Tasks\LargeFileUploadTask;
use Microsoft\Graph\Generated\Models\AttachmentItem;
use Microsoft\Graph\Generated\Models\AttachmentType;
use Microsoft\Graph\Generated\Models\CreateUploadSessionPostRequestBody;
use Psr\Http\Client\NetworkExceptionInterface;
use GuzzleHttp\Psr7\Utils;

class ArchivoController extends Controller
{
   public function upload_file(Request $request)
    {
        try {
            $request->validate([
                'folder_id' => 'required|exists:carpetas,id',
                'file' => 'required|array', // Cambiado a array para manejar múltiples archivos
                'file.*' => 'file|max:4147483648', // Cada archivo individual debe ser un archivo con un tamaño máximo de 4G
            ]);
    
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
            // Obtener el nuevo token y reintentar
            $oneDriveToken = GoogleToken::find(1);
            $accessToken = $oneDriveToken->access_token;
            $folderId = $request->folder_id;
            $carpeta = Carpeta::find($folderId);
            $folder_onedrive_id = $carpeta->google_drive_folder_id;
            $files = $request->file('file');
    
            foreach ($files as $file) {
                // Nombre y tipo MIME del archivo
                $name = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
    
                // Guarda el archivo temporalmente en storage
                $tempPath = $file->store('tmp');
    
                // Obtén la ruta completa del archivo en storage
                $filepath = storage_path('app/' . $tempPath);
    
                // Leer el contenido del archivo
                $fileContents = fopen($filepath, 'r');
                $client = new Client();
    
                // Subir el archivo a OneDrive
                $response = $client->put('https://graph.microsoft.com/v1.0/me/drive/items/' . $folder_onedrive_id . ':/' . $name . ':/content', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => $mimeType,
                        'Accept' => '*/*',
                    ],
                    'body' => file_get_contents($filepath), // Enviamos el contenido del archivo directamente en el cuerpo
                ]);
    
                fclose($fileContents); // Cerrar el archivo
                Storage::delete($tempPath); // Eliminar el archivo temporal
    
                $uploadItem = json_decode($response->getBody()->getContents(), true);
    
                $uploadIdFile = $uploadItem['id'];
    
                // Generar el enlace de edición
                $editLinkResponse = $client->post("https://graph.microsoft.com/v1.0/me/drive/items/{$uploadIdFile}/createLink", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'type'  => 'edit',
                        'scope' => 'anonymous',
                    ],
                ]);
    
                $editLinkBody = json_decode($editLinkResponse->getBody(), true);
                $editLink = $editLinkBody['link']['webUrl'] ?? null;
    
                // Guardar los detalles del archivo en la base de datos
                $uploadedfile = new Archivo;
                $uploadedfile->user_id = Auth::id();
                $uploadedfile->nombre = $name;
                $uploadedfile->google_drive_file_id = $uploadIdFile; // ID del archivo subido
                $uploadedfile->carpeta_id = $request->folder_id;
                $uploadedfile->mime_type = $mimeType;
                $uploadedfile->edit_link = $editLink; // Guardar el enlace de edición
                $uploadedfile->save();
    
                // Registrar la actividad en el historial
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'file_id' => $uploadedfile->id,
                    'action' => 'Ha Cargado',
                    'performed_at' => now(),
                ]);
    
                event(new ArchivoCreado($uploadedfile));
            }
    
            return redirect()->back()->with('mensaje', 'Todos los archivos se subieron exitosamente.')
                ->with('icono', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje', 'Error inesperado: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }
    
    public function buscar(Request $request)
    {
        $searchTerm = $request->input('search');
    
        // Buscar carpetas
        $carpetas = Carpeta::where('nombre', 'like', '%' . $searchTerm . '%')->get();
    
        // Buscar archivos
        $archivos = Archivo::where('nombre', 'like', '%' . $searchTerm . '%')->get();
    
        return view('admin.mi_unidad.index', compact('carpetas', 'archivos'));
    }
        
    public function renameFile(Request $request, $id)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'new_name' => 'required|string|max:255',
            ]);

            // Obtener el archivo
            $archivo = Archivo::findOrFail($id);

            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
            // Obtener el token de OneDrive
            $oneDriveToken = GoogleToken::find(1); // Asegúrate de que este modelo y método sean correctos para OneDrive
            $accessToken = $oneDriveToken->access_token;

            // Obtener el ID del archivo en OneDrive
            $onedriveFileId = $archivo->google_drive_file_id; // Considera renombrar a 'onedrive_file_id' para mayor claridad


            $updatedMetadata = [
                'name' => $request->new_name, // Nombre nuevo del archivo
            ];

            $response = Http::withToken($accessToken)
            ->patch("https://graph.microsoft.com/v1.0/me/drive/items/{$onedriveFileId}", $updatedMetadata);
            // Verificar la respuesta
            if ($response->getStatusCode() === 200) {
                // Actualizar el nombre en la base de datos
                $archivo->nombre = $request->new_name;
                $archivo->save();

                // Registrar la actividad en el historial
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'file_id' => $archivo->id,
                    'action' => 'Ha Renombrado',
                    'performed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Archivo renombrado exitosamente.',
                    'new_name' => $archivo->nombre,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo renombrar el archivo en OneDrive.',
                ], 500);
            }
        } catch (\Exception $e) {
            

            return response()->json([
                'success' => false,
                'message' => 'Error inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    
   
    
    
    
    /**
     * Display the specified resource.
     */
    public function getFiles($folder_id)
    {
        try {
            $userId = auth()->id();

            // Obtener la carpeta con archivos y permisos del usuario
            $folder = Carpeta::with(['archivos.permisos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->findOrFail($folder_id);

            $archivos = $folder->archivos;

            $client = new Client();

            $accessToken = $this->obtenerAccessToken(); // Implementa este método según tu lógica

            $promises = [];

            foreach ($archivos as $archivo) {
                // Verifica si el archivo ya tiene un edit_url en caché
                $cacheKey = "edit_url_{$archivo->id}";
                $embedUrl = Cache::get($cacheKey);

                if ($embedUrl) {
                    $archivo->edit_url = $embedUrl;
                } else {
                    // Crear una promesa para generar el enlace
                    $promises[$archivo->id] = $client->postAsync("https://graph.microsoft.com/v1.0/me/drive/items/{$archivo->google_drive_file_id}/createLink", [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type'  => 'application/json',
                        ],
                        'json' => [
                            'type'  => 'view',       // Tipo de enlace: 'view' para solo visualización
                            'scope' => 'anonymous',  // Alcance: 'anonymous' para acceso sin autenticación
                        ],
                    ]);
                }
            }

            // Esperar a que todas las promesas se resuelvan
            $results = Utils::settle($promises)->wait();

            foreach ($results as $archivoId => $result) {
                if ($result['state'] === 'fulfilled') {
                    $response = $result['value'];
                    $linkData = json_decode($response->getBody(), true);

                    if (isset($linkData['link']['webUrl'])) {
                        $embedUrl = $linkData['link']['webUrl'];
                        $archivo = $archivos->find($archivoId);
                        $archivo->edit_url = $embedUrl;

                        // Almacenar en caché
                        $cacheKey = "edit_url_{$archivo->id}";
                        Cache::put($cacheKey, $embedUrl, now()->addMinutes(60));
                    }
                } else {
                    Log::error("Error al generar enlace para Archivo ID {$archivoId}: " . $result['reason']);
                    // Opcional: Asignar un valor por defecto o nulo
                    $archivo = $archivos->find($archivoId);
                    $archivo->edit_url = null;
                }
            }

            return response()->json($folder);
        } catch (\Exception $e) {
            Log::error("Error al obtener archivos para Carpeta ID {$folder_id}: " . $e->getMessage());
            return response()->json([
                'archivos' => [],
                'message' => 'No se pudieron obtener los archivos.',
            ], 500);
        }
    }


    /**
     * Download the specified resource.
     */
    public function download($id)
    {
        $archivo = Archivo::findOrFail($id);

        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();
        // Obtener el nuevo token y reintentar
        $oneDriveToken = GoogleToken::find(1);
        $accessToken = $oneDriveToken->access_token;
        // Usar la API de Microsoft Graph para descargar el archivo
        $client = new Client();
        try {
            // Reemplaza con la URL correcta de OneDrive
            $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$archivo->google_drive_file_id}/content"; // Asegúrate de usar el ID correcto del archivo en OneDrive

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/octet-stream', // O el MIME type apropiado
                ]
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'file_id' => $archivo->id,
                'action' => 'Ha Descargado',
                'performed_at' => now(),
            ]);

            return response($response->getBody(), 200)
                ->header('Content-Type', $response->getHeader('Content-Type'))
                ->header('Content-Disposition', 'attachment; filename="' . $archivo->nombre . '"');
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje', 'Error inesperado: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }
    /**
     * Edit the specified resource.
     */
    public function edit($id)
    {
        $archivo = Archivo::findOrFail($id);
        $mimeType = $archivo->mime_type;
        $embedUrl = null;

        // Verificar tipo de archivo para determinar la URL de edición
        if (strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') !== false) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'file_id' => $archivo->id,
                'action' => 'Ha editado el documento',
                'performed_at' => now(),
            ]);
            // La URL debe tener el `resid` de OneDrive
            $embedUrl = "https://onedrive.live.com/edit.aspx?resid={$archivo->google_drive_file_id}&cid={$archivo->google_drive_file_id}";
        } elseif (strpos($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'file_id' => $archivo->id,
                'action' => 'Ha editado el documento Excel',
                'performed_at' => now(),
            ]);
            // También la URL para documentos de Excel debe estar configurada correctamente con `resid`
            $embedUrl = "https://onedrive.live.com/edit.aspx?resid={$archivo->google_drive_file_id}&cid={$archivo->google_drive_file_id}";
        } else {
            return redirect()->back()->with('error', 'Este tipo de archivo no se puede editar.');
        }

        // Redirige a la URL de edición en lugar de mostrar un iframe
        return redirect()->to($embedUrl);
    }

    public function moverAPapelera(Request $request,$id)
    {
        $archivo = Archivo::findOrFail($id);
        $archivo->user_id = auth()->user()->id;
        $archivo->delete();
        ActivityLog::create([
            'user_id' => auth()->id(),
            'file_id' => $archivo->id,
            'action' => 'Ha Eliminado',
            'performed_at' => now(),
        ]);
        return response()->json(['message' => 'Archivo eliminado exitosamente.']);
    }

    // Restaurar archivo de la papelera
    public function restaurar($id)
    {
        $archivo = Archivo::withTrashed()->findOrFail($id);
        $archivo->restore();
        ActivityLog::create([
            'user_id' => auth()->id(),
            'file_id' => $archivo->id,
            'action' => 'Ha Restaurado',
            'performed_at' => now(),
        ]);
        return redirect()->back()->with('success', 'El archivo ha sido restaurado.');
    }
    
    public function show($id)
    {
        $archivo = Archivo::findOrFail($id);
        $user = auth()->user(); // Obtener el usuario autenticado
        $carpeta = $archivo->carpeta; // Suponiendo que cada archivo pertenece a una carpeta
    
        // Verificar si el usuario tiene permiso para ver la carpeta
        if (!$carpeta->tienePermiso($user->id, 'ver')) {
            return redirect()->back()->with('error', 'No tienes permiso para ver esta carpeta.');
        }
    
        // Verificar si el usuario tiene permiso para ver el archivo
        if (!$archivo->tienePermisoVer($user->id)) {
            return redirect()->back()->with('error', 'No tienes permiso para ver este archivo.');
        }
    
        // Registrar la actividad
        ActivityLog::create([
            'user_id' => auth()->id(),
            'file_id' => $archivo->id,
            'action' => 'Ha visualizado',
            'performed_at' => now(),
        ]);
    
        // Continuar con la lógica existente para GoogleDrive y OneDrive
        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();
        // Obtener el nuevo token y reintentar
        $oneDriveToken = GoogleToken::find(1);
        $accessToken = $oneDriveToken->access_token;
        // Crear un cliente HTTP
        $client = new Client();
    
        try {
            // Generar un enlace de compartición con permisos de solo lectura
            $response = $client->post("https://graph.microsoft.com/v1.0/me/drive/items/{$archivo->google_drive_file_id}/createLink", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'type' => 'embed',
                    'scope' => 'anonymous',
                ],
            ]);
    
            $body = json_decode($response->getBody(), true);
            $embedUrl = $body['link']['webUrl'];
    
            // Retornar la vista que contiene el iframe
            return view('admin.archivo.embed', compact('archivo', 'embedUrl'));
    
        } catch (\Exception $e) {
            // Manejar errores y mostrar un mensaje al usuario
            return redirect()->back()->with('error', 'Error al obtener el enlace del archivo: ' . $e->getMessage());
        }
    }


    // Eliminar archivo permanentemente
    public function eliminarPermanentemente($id)
    {
        $archivo = Archivo::withTrashed()->findOrFail($id);
        $archivo->forceDelete();
        $archivo = Archivo::onlyTrashed()->where('id', $id)
            ->whereHas('carpeta', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        // Aplicar la política de archivo
        $this->authorize('delete', $archivo);

        $path = storage_path('app/public/papelera/' . $archivo->nombre);
        $restorePath = storage_path('app/public/' . $archivo->carpeta->nombre . '/' . $archivo->nombre);

        if (file_exists($path)) {
            rename($path, $restorePath);
        }

        // Actualizar la base de datos para restaurar el archivo
        $archivo->update(['deleted_at' => null]);

        return redirect()->back()->with('success', 'El archivo ha sido eliminado permanentemente.');
    }

   public function verPapelera()
    {
       
            $archivos = Archivo::onlyTrashed()->paginate(10);
            $carpetas = Carpeta::onlyTrashed()->paginate(10);
        
    
        return view('admin.mi_unidad.papelera', compact('archivos', 'carpetas'));
    }

    public function toggleVisibility(Request $request, $id)
    {
        $archivo = Archivo::findOrFail($id);
        $permiso = ArchivoPermiso::where('user_id', auth()->id())->where('archivo_id', $id)->firstOrFail();

        // Cambiar el estado de visibilidad
        $permiso->ver = $request->input('visible');
        $permiso->save();

        return response()->json(['success' => true]);
    }

    public function updatePermissions(Request $request, Archivo $archivo)
    {
        // Validar la entrada
        $validated = $request->validate([
            'ver' => 'boolean',
            'descargar' => 'boolean',
            'editar' => 'boolean',
            'eliminar' => 'boolean',
            'user_id' => 'required|exists:users,id', // Asegúrate de que el user_id existe
            'file_id' => 'required|exists:archivos,id', // Asegúrate de que el file_id exista
    
        ]);
    
        // Obtener el ID del usuario que estás explorando
        $userId = $validated['user_id'];
        $fileId = $validated['file_id'];
    
        // Obtener o crear permisos para el usuario específico
        $permiso = archivo::find($fileId)->permisos()->firstOrCreate(['user_id' => $userId]);
    
        // Actualizar los permisos
        $permiso->update($validated);
    
        return redirect()->back()->with('success', 'Permisos Actualizados');
    }
    
    



}

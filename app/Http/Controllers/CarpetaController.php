<?php

namespace App\Http\Controllers;


use App\Models\Carpeta;
use App\Models\Archivo;
use App\Events\CarpetaCreada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GoogleAuthController;
use App\Models\GoogleToken;
use Illuminate\Support\Facades\Log;


class CarpetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $viewedUserId = $request->input('user_id', $user->id); // ID del usuario que estamos viendo, por defecto el actual

        // Obtener carpetas donde el usuario tiene permiso para ver
        $carpetas = Carpeta::where(function ($query) use ($viewedUserId, $user) {
            // Buscar carpetas del usuario visualizado o de otros usuarios a los que el usuario tiene acceso
            $query->where('user_id', $viewedUserId)
                ->orWhereHas('permisos', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id)
                                ->where('ver', true); // Asegurarse de que tiene permiso de ver
                });
        })
        ->whereNull('carpeta_padre_id')
        ->where('estado', true) // Solo carpetas con estado verdadero
        ->get();

        // Retornar solo las carpetas que tienen permiso de ver
        $carpetas = $carpetas->filter(function ($carpeta) use ($user) {
            return $carpeta->permisos->contains('user_id', $user->id) && $carpeta->permisos->contains('ver', true);
        });
        
        // No se cargan archivos en la vista principal sin un término de búsqueda
        $archivos = [];

        return view('admin.mi_unidad.index', compact('carpetas', 'archivos'));
    }
    
    
    public function buscar(Request $request)
    {
        $searchTerm = $request->input('search');
        
        // Buscar carpetas que coincidan con el término de búsqueda
        $carpetas = Carpeta::where('nombre', 'like', '%' . $searchTerm . '%')->get();
        
        // Buscar archivos que coincidan con el término de búsqueda
        $archivos = collect(Archivo::where('nombre', 'like', '%' . $searchTerm . '%')->get());  // Usamos collect() para asegurarnos de que sea una colección
        
        // Pasar ambos a la vista con el término de búsqueda
        return view('admin.mi_unidad.index', compact('carpetas', 'archivos', 'searchTerm'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    Log::info('Datos recibidos en la solicitud:', $request->all());

    // Validar los datos recibidos
    $validatedData = $request->validate([
        'nombre' => 'required|string|max:255',
        'color' => 'nullable|string',
        'carpeta_padre_id' => 'nullable|exists:carpetas,id'
    ]);

    Log::info('Datos validados:', $validatedData);

    $GoogleAuthController = new GoogleAuthController();
    $GoogleAuthController->refreshAccessToken();
    // Obtener el nuevo token y reintentar
    $oneDriveToken = GoogleToken::find(1);
    $accessToken = $oneDriveToken->access_token;

    Log::info('Token de acceso obtenido:', ['access_token' => $accessToken]);

    $userId = auth()->id();
    $nombreCarpeta = $request->input('nombre');
    $carpetaPadreId = $request->input('carpeta_padre_id');
    $color = $request->input('color');

    Log::info('Datos de la carpeta:', [
        'user_id' => $userId,
        'nombre' => $nombreCarpeta,
        'carpeta_padre_id' => $carpetaPadreId,
        'color' => $color
    ]);

    // Obtener el ID de la carpeta padre en OneDrive (si existe)
    $carpetaPadre = Carpeta::find($carpetaPadreId);
    $parentFolderId = $carpetaPadre ? $carpetaPadre->google_drive_folder_id : null;

    Log::info('ID de la carpeta padre en OneDrive:', ['parentFolderId' => $parentFolderId]);

    try {
        // Crear la carpeta en OneDrive
        $endpoint = $parentFolderId
            ? "https://graph.microsoft.com/v1.0/me/drive/items/{$parentFolderId}/children"
            : "https://graph.microsoft.com/v1.0/me/drive/root/children";

        Log::info('Endpoint de la API de OneDrive:', ['endpoint' => $endpoint]);

        $response = Http::withToken($accessToken)->post($endpoint, [
            'name' => $nombreCarpeta,
            'folder' => new \stdClass(), // Indica que es una carpeta
            '@microsoft.graph.conflictBehavior' => 'rename' // Evita conflictos de nombres
        ]);

        Log::info('Respuesta de la API de OneDrive:', ['response' => $response->json(), 'status' => $response->status()]);

        if ($response->successful()) {
            $oneDriveFolder = $response->json();

            $one_drive_folder_id = $oneDriveFolder['id'];
            // Guardar la carpeta en la base de datos
            $carpeta = Carpeta::create([
                'nombre' => $nombreCarpeta,
                'carpeta_padre_id' => $carpetaPadreId,
                'user_id' => $userId,
                'google_drive_folder_id' =>  $one_drive_folder_id, // ID de la carpeta en OneDrive
                'color' => $color
            ]);

            Log::info('Carpeta creada en la base de datos:', $carpeta->toArray());

            event(new CarpetaCreada($carpeta));
            return redirect()->back()
                ->with('mensaje', 'Carpeta creada exitosamente en el sistema y OneDrive.')
                ->with('icono', 'success');
        } elseif ($response->status() === 401) {
            Log::warning('Token expirado, intentando refrescar el token.');

            // Refrescar el token si expiró
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();

            // Obtener el nuevo token y reintentar
            $oneDriveToken = GoogleToken::find(1);
            $accessToken = $oneDriveToken->access_token;

            Log::info('Nuevo token de acceso obtenido:', ['access_token' => $accessToken]);

            $response = Http::withToken($accessToken)->post($endpoint, [
                'name' => $nombreCarpeta,
                'folder' => new \stdClass(),
                '@microsoft.graph.conflictBehavior' => 'rename'
            ]);

            Log::info('Respuesta de la API de OneDrive después de refrescar el token:', ['response' => $response->json(), 'status' => $response->status()]);

            if ($response->successful()) {
                $oneDriveFolder = $response->json();

                $carpeta = Carpeta::create([
                    'nombre' => $nombreCarpeta,
                    'carpeta_padre_id' => $carpetaPadreId,
                    'user_id' => $userId,
                    'google_drive_folder_id' => $oneDriveFolder['id'],
                    'color' => $color
                ]);

                Log::info('Carpeta creada en la base de datos después de refrescar el token:', $carpeta->toArray());

                event(new CarpetaCreada($carpeta));
                return redirect()->back()
                    ->with('mensaje', 'Carpeta creada exitosamente en el sistema y OneDrive.')
                    ->with('icono', 'success');
            }
        }

        Log::error('Error al crear la carpeta en OneDrive:', ['response' => $response->json()]);
        return redirect()->back()->with('mensaje', 'Error al crear la carpeta en OneDrive: ' . $response->json()['error']['message'])
            ->with('icono', 'error');
    } catch (\Exception $e) {
        Log::error('Error inesperado:', ['exception' => $e->getMessage()]);
        return redirect()->back()->with('mensaje', 'Error inesperado: ' . $e->getMessage())
            ->with('icono', 'error');
    }
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $carpeta = Carpeta::with(['permisos' => function ($query) {
            $query->where('user_id', auth()->id())
            ->where('ver', true);
        }])
        ->where('id', $id)
        ->firstOrFail();
        // Verificar el permiso de 'ver'
        $permisos = $carpeta->permisos->first();

        // Obtener las subcarpetas que el usuario puede ver
        $subcarpetas = Carpeta::with(['permisos' => function ($query) {
            $query->where('user_id', auth()->id());
        }])
        ->where('carpeta_padre_id', $carpeta->id)
        ->whereHas('permisos', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('ver', true);
        })
        ->get();

        // Obtener los archivos que el usuario puede ver
        $archivos = $carpeta->archivos()->with(['permisos' => function ($query) {
            $query->where('user_id', auth()->id());
        }])
        ->whereHas('permisos', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('ver', true);
        })
        ->get();



        return view('admin.mi_unidad.show', compact('carpeta', 'subcarpetas', 'archivos', 'permisos'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $carpeta = Carpeta::findOrFail($id);
        return view('admin.mi_unidad.edit', compact('carpeta'));
    }


    public function store_subfolder(Request $request)
    {
        
    

        $onedriveToken = GoogleToken::find(1); // Suponiendo que guardas el token de OneDrive
        $accessToken = $onedriveToken->access_token;
        $user = auth()->user();
        $userId = $user->id;
        $nombreCarpeta = $request->input('nombre');
        $carpetaPadreId = $request->input('carpeta_padre_id');
        $color = $request->input('color');


        // Crear la subcarpeta en OneDrive
        try {
            // Obtener el ID de la carpeta padre
            $carpetaPadre = Carpeta::find($carpetaPadreId);

            // Crear la nueva subcarpeta en OneDrive
            $url = 'https://graph.microsoft.com/v1.0/me/drive/items/' . $carpetaPadre->google_drive_folder_id . '/children'; // Usamos el ID de la carpeta padre en OneDrive
            $response = Http::withToken($accessToken)->post($url, [
                'name' => $nombreCarpeta,
                'folder' => new \stdClass(), // Especificar que es una carpeta
                '@microsoft.graph.conflictBehavior' => 'rename', // Si hay un conflicto, renombrar
            ]);

            // Verificar si la carpeta fue creada exitosamente
            if ($response->successful()) {
                $onedriveFolder = $response->json();
                $one_drive_folder_id = $onedriveFolder['id'];
                // Guardar la nueva subcarpeta en la base de datos
                $carpeta = Carpeta::create([
                    'nombre' => $nombreCarpeta,
                    'carpeta_padre_id' => $carpetaPadreId,
                    'user_id' => $userId,
                    'google_drive_folder_id' => $one_drive_folder_id, // Guardar el ID de la subcarpeta de OneDrive
                    'color' => $color,
                ]);

                event(new CarpetaCreada($carpeta));

                return redirect()->back()
                    ->with('mensaje', 'Subcarpeta creada exitosamente en el sistema y OneDrive.')
                    ->with('icono', 'success');
            } elseif ($response->status() === 401) {
                // Si el token ha expirado, refrescarlo
                $onedriveAuthController = new GoogleAuthController();
                $onedriveAuthController->refreshAccessToken();

                // Obtener el nuevo token de acceso
                $onedriveToken = GoogleToken::find(1);
                $accessToken = $onedriveToken->access_token;

                // Reintentar la creación de la subcarpeta
                $response = Http::withToken($accessToken)->post($url, [
                    'name' => $nombreCarpeta,
                    'folder' => new \stdClass(),
                    '@microsoft.graph.conflictBehavior' => 'rename',
                ]);

                // Verificar si la carpeta fue creada exitosamente
                if ($response->successful()) {
                    $onedriveFolder = $response->json();
                    $one_drive_folder_id = $onedriveFolder['id'];
                    // Guardar la nueva subcarpeta en la base de datos
                    $carpeta = Carpeta::create([
                        'nombre' => $nombreCarpeta,
                        'carpeta_padre_id' => $carpetaPadreId,
                        'user_id' => $userId,
                        'google_drive_folder_id' => $one_drive_folder_id,
                        'color' => $color,
                    ]);

                    event(new CarpetaCreada($carpeta));

                    return redirect()->back()
                        ->with('mensaje', 'Subcarpeta creada exitosamente en el sistema y OneDrive.')
                        ->with('icono', 'success');
                }
            }
            // Manejar errores en la creación en OneDrive
            return redirect()->back()->with('mensaje', 'Error al crear la subcarpeta en OneDrive: ' . $response->json()['error']['message'])
                ->with('icono', 'error');
        } catch (\Exception $e) {
            // Manejar excepciones
            return redirect()->back()->with('mensaje', 'Error inesperado: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update_folder(Request $request)
    {
        try {
            // Validar los datos del formulario con mensajes personalizados
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:100',
                'color' => 'string|max:100',
            ], [
                'nombre.required' => 'El nombre de la carpeta es obligatorio.',
                'nombre.string' => 'El nombre de la carpeta debe ser una cadena de texto.',
                'nombre.max' => 'El nombre de la carpeta no puede exceder los 100 caracteres.',
                'color.string' => 'El colo de la carpeta debe ser valido',
                'color.max' => 'EL color de la carpeta no existe',
            ]);
            
            // Log para ver si los datos del formulario fueron validados correctamente
            Log::info('Datos validados:', $validatedData);
    
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
    
            // Registrar si la tokenización de Google es exitosa
            $googleToken = GoogleToken::find(1);
            Log::info('Google Token obtenido:', ['access_token' => $googleToken->access_token]);
    
            // Encontrar la carpeta por su ID
            $carpeta = Carpeta::findOrFail($request->id);
            Log::info('Carpeta encontrada:', ['carpeta' => $carpeta]);
    
            $accessToken = $googleToken->access_token;
    
            // Obtener el ID de la carpeta en Google Drive (o OneDrive, como mencionas)
            $one_drive_folder_id = $carpeta->google_drive_folder_id;
            Log::info('ID de la carpeta en OneDrive:', ['folder_id' => $one_drive_folder_id]);
    
            // Preparar la actualización del nombre de la carpeta
            $updatedMetadata = [
                'name' => $validatedData['nombre'],
            ];
            Log::info('Metadata preparada para actualización:', $updatedMetadata);
    
            // Realizar la solicitud PATCH a OneDrive para actualizar el nombre de la carpeta
            $response = Http::withToken($accessToken)
                ->patch("https://graph.microsoft.com/v1.0/me/drive/items/{$one_drive_folder_id}", $updatedMetadata);
    
            // Log para verificar la respuesta de la API de OneDrive
            Log::info('Respuesta de OneDrive:', ['response' => $response->body()]);
    
            // Actualizar la carpeta en la base de datos
            $carpeta->nombre = $validatedData['nombre'];
            $carpeta->color = $validatedData['color'];
            $carpeta->save();
    
            // Log para verificar que la carpeta se haya actualizado correctamente
            Log::info('Carpeta actualizada en la base de datos:', ['carpeta' => $carpeta]);
    
            // Redirigir al usuario con un mensaje de éxito
            return redirect()->route('file-system.home')
                ->with('mensaje', 'Carpeta actualizada exitosamente en el sistema y Google Drive.')
                ->with('icono', 'success');
    
        } catch (\Exception $e) {
            // Capturar cualquier excepción y redirigir con un mensaje de error
            Log::error('Error al actualizar la carpeta:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('mensaje', $e->getMessage())
                ->with('icono', 'error');
        }
    }



    public function update_subfolder(Request $request)
    {
        // Validar los datos del formulario con mensajes personalizados
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100',
        ], [
            'nombre.required' => 'El nombre de la carpeta es obligatorio.',
            'nombre.string' => 'El nombre de la carpeta debe ser una cadena de texto.',
            'nombre.max' => 'El nombre de la carpeta no puede exceder los 100 caracteres.',
        ]);

        // Encontrar la carpeta por su ID
        $carpeta = Carpeta::findOrFail($request->id);

        // Verificar si ya existe otra carpeta con el mismo nombre dentro del mismo carpeta_padre_id
        $exists = Carpeta::where('nombre', $validatedData['nombre'])
            ->where('carpeta_padre_id', $carpeta->carpeta_padre_id)
            ->where('id', '!=', $carpeta->id) // Excluir la carpeta actual de la búsqueda
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('mensaje', 'Ese nombre de Carpeta ya está en uso en esta ubicación. Por favor, intente otro nombre.')
                ->with('icono', 'error');
        }


        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();


        $googleToken = GoogleToken::find(1);
        $accessToken = $googleToken->access_token;
        $one_drive_folder_id=$carpeta->google_drive_folder_id;

        // Renombrar la subcarpeta en Google Drive
        try {
            $response = Http::withToken($accessToken)->patch('https://graph.microsoft.com/v1.0/me/drive/items/' . $one_drive_folder_id, [
                'name' => $validatedData['nombre'],
            ]);

            // Verificar si el renombrado fue exitoso
            if ($response->successful()) {
                // Actualizar la subcarpeta con el nuevo nombre en la base de datos
                $carpeta->nombre = $validatedData['nombre'];
                $carpeta->save();

                // Redirigir al usuario de vuelta a la carpeta padre con un mensaje de éxito
                $carpeta_padre_id = $carpeta->carpeta_padre_id;
                return redirect()->route('mi_unidad.show', ['id' => $carpeta_padre_id])
                    ->with('mensaje', 'Subcarpeta actualizada exitosamente en el sistema y Google Drive.')
                    ->with('icono', 'success');
            } elseif ($response->status() === 401) {
                $carpeta_padre_id = $carpeta->carpeta_padre_id;
                // Intentar refrescar el token
                $googleDriveController = new GoogleAuthController();
                $googleDriveController->refreshAccessToken();

                // Obtener el nuevo token de acceso después de refrescar
                $googleToken = GoogleToken::find(1);
                $accessToken = $googleToken->access_token;

                $response = Http::withToken($accessToken)->patch('https://graph.microsoft.com/v1.0/me/drive/items/' . $one_drive_folder_id, [
                    'name' => $validatedData['nombre'],
                ]);

                // Verificar si el renombrado fue exitoso

                if ($response->successful()) {
                    // Actualizar la subcarpeta con el nuevo nombre en la base de datos
                    $carpeta->nombre = $validatedData['nombre'];
                    $carpeta->color  = $request->color;
                    $carpeta->save();

                    return redirect()->route('mi_unidad.show', ['id' => $carpeta_padre_id])
                        ->with('mensaje', 'Subcarpeta actualizada exitosamente en el sistema y Google Drive.')
                        ->with('icono', 'success');
                }
            }

            // Manejar errores en el renombrado en Google Drive
            return redirect()->back()->with('mensaje', 'Error al actualizar la subcarpeta en Google Drive: ' . $response->json()['error']['message'])
                ->with('icono', 'error');
        } catch (\Exception $e) {
            // Manejar excepciones
            return redirect()->back()->with('mensaje', 'Error inesperado: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }





    /**
     * Remove the specified resource from storage.
     */
    // Método para eliminar una carpeta
    public function destroy($id)
    {
        try {
            // Encontrar la carpeta por su ID
            $carpeta = Carpeta::findOrFail($id);

            // Eliminar la carpeta
            $carpeta->delete();
            // Redirigir con un mensaje de éxito
            return redirect()->route('file-system.home')
                ->with('mensaje', 'Carpeta eliminada exitosamente.')
                ->with('icono', 'success');
        } catch (\Exception $e) {
            // Manejar errores
            return redirect()->back()
                ->with('mensaje', 'Ocurrió un error al intentar eliminar la carpeta.' )
                ->with('icono', 'error');
        }
    }

    public function destroy_subfolder($id)
    {
        try {
            // Encontrar la subcarpeta por su ID
            $subcarpeta = Carpeta::findOrFail($id);
            // Obtener el ID de la carpeta padre antes de la eliminación
            $carpeta_padre_id = $subcarpeta->carpeta_padre_id;
            // Eliminar la subcarpeta
            $subcarpeta->delete();

            // Redirigir al usuario a la carpeta padre con un mensaje de éxito
            return redirect()->route('mi_unidad.index')
                ->with('mensaje', 'Subcarpeta eliminada exitosamente.')
                ->with('icono', 'success');
        } catch (\Exception $e) {
            // Manejar errores
            return redirect()->back()
                ->with('mensaje', 'Ocurrió un error al intentar eliminar la subcarpeta.')
                ->with('icono', 'error');
        }
    }


    public function restaurar($id)
    {
        // Encuentra la carpeta con los archivos que estaban en soft delete
        $carpeta = Carpeta::withTrashed()->with('archivos')->findOrFail($id);
        // Restaura la carpeta
        $carpeta->restore();
        // Restaura todos los archivos asociados a la carpeta
        foreach ($carpeta->archivos()->onlyTrashed()->get() as $archivo) {
            $archivo->restore();
        }
        return redirect()->route('mi_unidad.index')
                ->with('mensaje', 'Restauración Exitosa.')
                ->with('icono', 'success');
    }

    public function eliminarPermanente($id)
    {
        // Buscar la carpeta eliminada con soft delete
        $carpeta = Carpeta::withTrashed()->findOrFail($id);
     // Refrescar el token de acceso de OneDrive
        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();

        $oneDriveToken = GoogleToken::find(1); // Asumiendo que el token de OneDrive está guardado aquí
        $accessToken = $oneDriveToken->access_token;
        // Obtener el ID de la carpeta de OneDrive asociada
        $oneDriveFolderId = $carpeta->google_drive_folder_id; // Asegúrate de tener este campo en tu base de datos
    
        // Verificar si existe el ID de la carpeta en OneDrive
        if ($oneDriveFolderId) {
    
            // Hacer la solicitud para eliminar la carpeta en OneDrive
            try {
                $response = Http::withToken($accessToken)->delete('https://graph.microsoft.com/v1.0/me/drive/items/' . $oneDriveFolderId);
    
                // Verificar si la eliminación fue exitosa
                if ($response->successful()) {
                    // Si la carpeta se eliminó correctamente en OneDrive, proceder con la eliminación en la base de datos
                    $carpeta->forceDelete();
    
                    return redirect()->route('archivo.papelera')
                        ->with('mensaje', 'La carpeta y su contenido han sido eliminados permanentemente, tanto en el sistema como en OneDrive.')
                        ->with('icono', 'success');
                } else {
                    // En caso de error al eliminar de OneDrive
                    return redirect()->route('archivo.papelera')
                        ->with('mensaje', 'Hubo un error al eliminar la carpeta en OneDrive.')
                        ->with('icono', 'error');
                }
            } catch (\Exception $e) {
                // Manejo de errores
                return redirect()->route('archivo.papelera')
                    ->with('mensaje', 'Error al comunicarse con OneDrive: ' . $e->getMessage())
                    ->with('icono', 'error');
            }
        } else {
            // Si no se encontró el ID de la carpeta en OneDrive
            $carpeta->forceDelete();
    
            return redirect()->route('archivo.papelera')
                ->with('mensaje', 'La carpeta ha sido eliminada permanentemente en el sistema, pero no se encontró en OneDrive.')
                ->with('icono', 'warning');
        }
    }


    public function marcarObsoleta($id)
    {
        $carpeta = Carpeta::findOrFail($id);
        $carpeta->marcarComoObsoleta();

        return redirect()->route('file-system.home')->with('mensaje', 'Carpeta marcada como obsoleta.');
    }

    public function reactivar($id)
    {
        $carpeta = Carpeta::findOrFail($id);
        $carpeta->marcarComoActiva();
        return redirect()->route('mi_unidad.index')->with('mensaje', 'Carpeta reactivada.');
    }

    public function obsoletas()
    {
        $carpetasObsoletas = Carpeta::where('estado', false)->with('carpetasHijas', 'archivos')->get();
        return view('admin.obsoletas.index', compact('carpetasObsoletas'));
    }

    public function showObsoleta($id)
    {
        // Obtener la carpeta obsoleta y sus subcarpetas y archivos
        $carpeta = Carpeta::with(['carpetasHijas', 'archivos'])->findOrFail($id);
        return view('admin.obsoletas.show', compact('carpeta'));
    }
    
    
    
    public function descargarContenidoCarpeta($folderId)
    {
        // Refrescar el token de acceso de OneDrive
        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();

        $oneDriveToken = GoogleToken::find(1); // Asumiendo que el token de OneDrive está guardado aquí
        $accessToken = $oneDriveToken->access_token;
        
        // Obtener la ID de la carpeta de OneDrive
        
        $carpeta = Carpeta::findOrFail($folderId);
         // Obtener el ID de la carpeta en Google Drive (o OneDrive, como mencionas)
        $one_drive_folder_id = $carpeta->google_drive_folder_id;
        $carpeta_nombre      = $carpeta->nombre;
        
        try {
            // Obtener el contenido de la carpeta (archivos) en OneDrive
            $response = Http::withToken($accessToken)->get("https://graph.microsoft.com/v1.0/me/drive/items/{$one_drive_folder_id}/children");

            if ($response->successful()) {
                // Recuperar la lista de archivos dentro de la carpeta
                $archivos = $response->json()['value'];

                // Crear un archivo ZIP para almacenar los archivos descargados
                $zipFilePath = storage_path("app/public/{$carpeta_nombre}_{$folderId}.zip");
                $zip = new \ZipArchive();

                if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== true) {
                    return redirect()->route('mi_unidad.index')
                        ->with('mensaje', 'No se pudo crear el archivo ZIP.')
                        ->with('icono', 'error');
                }

                // Descargar los archivos y agregarlos al archivo ZIP
                foreach ($archivos as $archivo) {
                    if (isset($archivo['file'])) {
                        // Obtener el ID del archivo y su nombre
                        $fileId = $archivo['id'];
                        $fileName = $archivo['name'];
                        // Descargar el archivo
                        $fileResponse = Http::withToken($accessToken)->get("https://graph.microsoft.com/v1.0/me/drive/items/{$fileId}/content");
                        if ($fileResponse->successful()) {
                            // Agregar el archivo al ZIP
                            $zip->addFromString($fileName, $fileResponse->body());
                        }
                    }
                }

                // Cerrar el archivo ZIP
                $zip->close();
                // Retornar el archivo ZIP para la descarga
                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                return redirect()->route('file-system.home')
                    ->with('mensaje', 'No se pudieron obtener los archivos de la carpeta.')
                    ->with('icono', 'error');
            }
        } catch (\Exception $e) {
            return redirect()->route('file-system.home')
                ->with('mensaje', 'Error al descargar los archivos: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }







}

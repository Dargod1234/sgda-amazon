<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archivo;
use App\Models\Carpeta;
use App\Models\User;
use App\Models\GoogleToken;
use App\Models\CarpetaPermiso;
use App\Models\ArchivoPermiso;
use App\Models\ActivityLog;
use App\Events\ArchivoCreado;
use App\Events\CarpetaCreada;

use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;




class FileSystemController extends Controller
{
    
       /**
     * Cliente HTTP para las solicitudes a Microsoft Graph
     */
    protected $client;
    
    /**
     * Token de acceso para OneDrive
     */
    protected $accessToken;
    
    /**
     * Tamaño del lote para subir archivos en paralelo
     */
    protected $batchSize = 5;
    
    
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 120, // Aumentamos el timeout para archivos grandes
            'http_errors' => false // Manejaremos los errores manualmente
        ]);
    }

    public function index()
    {
        // Obtiene el ID del usuario autenticado
        $userId = auth()->id();
    
        // Obtiene el usuario y las carpetas con sus permisos
        $user = User::findOrFail($userId);
        $folders = Carpeta::with([
            'carpetasHijas',
            'archivos.permisos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
            'permisos'
        ])
        ->where('estado', 1)
        ->whereNull('carpeta_padre_id')
        ->paginate(10);
    
        return view('file-system', compact('folders', 'user'));
    }

    public function getFolder()
    {
        $userId = auth()->id();
        $user = User::findOrFail($userId);
    
        $folders = Carpeta::with([
            'carpetasHijas',
            'archivos.permisos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
            'permisos'
        ])
        ->where('estado', 1)
        ->whereNull('carpeta_padre_id')
        ->paginate(10); // Cambia "10" al número de elementos por página deseado
    
        // Si la solicitud es AJAX, devolver JSON
        if (request()->ajax()) {
            return response()->json(['folders' => $folders, 'user' => $user]);
        }
    
        // De lo contrario, devolver la vista HTML
        return view('file-system', compact('folders', 'user'));
    }
    
   

   public function getFiles($folder_id)
    {
        try {
            $userId = auth()->id();
    
            // Obtener la carpeta con archivos y permisos del usuario
            $folder = Carpeta::with(['archivos.permisos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])->findOrFail($folder_id);
    
            // Los archivos ya contienen el `edit_link`, no es necesario regenerarlo
            $archivos = $folder->archivos;
    
            return response()->json([
                'folder' => $folder,
                'archivos' => $archivos,
            ]);
        } catch (\Exception $e) {
            Log::error("Error al obtener archivos para Carpeta ID {$folder_id}: " . $e->getMessage());
            return response()->json([
                'archivos' => [],
                'message' => 'No se pudieron obtener los archivos.',
            ], 500);
        }
    }
    
        
 public function uploadZip(Request $request)
    {
        try {
            $request->validate([
                'zipfile' => 'required|file|mimes:zip|max:4147483648',
                'folder_id' => 'required|exists:carpetas,id',
            ]);

            $folderId = $request->input('folder_id');
            $carpeta = Carpeta::find($folderId);
            
            // Refrescar el token al inicio (una sola vez)
            $this->refreshToken();

            // Preparar directorio temporal único para extracción
            $extractPath = sys_get_temp_dir() . '/unzipped_' . Str::uuid()->toString();
            File::ensureDirectoryExists($extractPath);
            
            // Extraer ZIP al directorio temporal
            $zipFile = $request->file('zipfile')->getRealPath();
            $zip = new ZipArchive;
            
            if ($zip->open($zipFile) !== TRUE) {
                return redirect()->back()
                    ->with('mensaje', 'Error al abrir el archivo ZIP')
                    ->with('icono', 'error');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Procesar el contenido del ZIP
            $result = $this->processZipContents(
                $extractPath, 
                $folderId, 
                $carpeta->google_drive_folder_id
            );
            
            // Limpiar directorio temporal
            File::deleteDirectory($extractPath);
            
            // Mostrar estadísticas del procesamiento
            return redirect()->back()
                ->with('mensaje', "ZIP procesado exitosamente. Se han subido {$result['archivos']} archivos en {$result['carpetas']} carpetas.")
                ->with('icono', 'success');
            
        } catch (\Exception $e) {
            Log::error('Error al procesar ZIP: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('mensaje', 'Error: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }
    
    /**
     * Procesa el contenido de un directorio ZIP extraído
     */
    private function processZipContents($extractPath, $parentFolderId, $parentFolderOnedriveId)
    {
        // Recopilar información sobre la estructura de carpetas y archivos
        $structure = $this->mapDirectoryStructure($extractPath);
        
        // Crear todas las carpetas primero (manteniendo la jerarquía)
        $createdFolders = $this->createFolderStructure(
            $structure['folders'], 
            $parentFolderId, 
            $parentFolderOnedriveId
        );
        
        // Procesar los archivos en lotes
        $processedFiles = $this->processFilesInBatches(
            $structure['files'], 
            $createdFolders
        );
        
        return [
            'carpetas' => count($createdFolders),
            'archivos' => $processedFiles
        ];
    }
    
    /**
     * Mapea la estructura de carpetas y archivos del directorio extraído
     */
    private function mapDirectoryStructure($directory)
    {
        $folders = [];
        $files = [];
        $rootPath = $directory;
        
        // Función recursiva para mapear la estructura
        $mapStructure = function ($path, $relativePath = '') use (&$mapStructure, &$folders, &$files, $rootPath) {
            // Añadir la carpeta actual a la lista
            if ($relativePath !== '') {
                $folders[] = [
                    'path' => $path,
                    'name' => basename($path),
                    'relative_path' => $relativePath,
                    'parent_path' => dirname($relativePath) === '.' ? '' : dirname($relativePath)
                ];
            }
            
            // Procesar archivos en la carpeta actual
            foreach (File::files($path) as $file) {
                $relativeFilePath = $relativePath ? $relativePath . '/' . $file->getFilename() : $file->getFilename();
                
                $files[] = [
                    'path' => $file->getPathname(),
                    'name' => $file->getFilename(),
                    'relative_path' => $relativeFilePath,
                    'folder_path' => $relativePath,
                    'mime_type' => mime_content_type($file->getPathname()),
                    'size' => $file->getSize()
                ];
            }
            
            // Procesar subcarpetas recursivamente
            foreach (File::directories($path) as $subdir) {
                $subdirName = basename($subdir);
                $newRelativePath = $relativePath ? $relativePath . '/' . $subdirName : $subdirName;
                $mapStructure($subdir, $newRelativePath);
            }
        };
        
        // Iniciar el mapeo desde el directorio raíz
        $mapStructure($directory);
        
        return [
            'folders' => $folders,
            'files' => $files
        ];
    }
    
    /**
     * Crea la estructura de carpetas en la base de datos y en OneDrive
     */
    private function createFolderStructure($folders, $parentFolderId, $parentFolderOnedriveId)
    {
        // Ordenar carpetas por profundidad para mantener jerarquía
        usort($folders, function($a, $b) {
            return substr_count($a['relative_path'], '/') <=> substr_count($b['relative_path'], '/');
        });
        
        $folderMap = [
            '' => [
                'db_id' => $parentFolderId,
                'onedrive_id' => $parentFolderOnedriveId
            ]
        ];
        
        // Crear las carpetas en orden por niveles
        foreach ($folders as $folder) {
            $parentPath = $folder['parent_path'];
            $folderName = $folder['name'];
            
            // Verificar si ya existe la carpeta padre en nuestro mapa
            if (!isset($folderMap[$parentPath])) {
                Log::warning("Carpeta padre no encontrada: {$parentPath}");
                continue;
            }
            
            $parentDbId = $folderMap[$parentPath]['db_id'];
            $parentOnedriveId = $folderMap[$parentPath]['onedrive_id'];
            
            // Verificar si la carpeta ya existe en la base de datos
            $existingFolder = Carpeta::where('nombre', $folderName)
                ->where('carpeta_padre_id', $parentDbId)
                ->first();
            
            if ($existingFolder) {
                // Usar la carpeta existente
                $folderMap[$folder['relative_path']] = [
                    'db_id' => $existingFolder->id,
                    'onedrive_id' => $existingFolder->google_drive_folder_id
                ];
            } else {
                // Crear nueva carpeta en la base de datos
                $newFolder = Carpeta::create([
                    'nombre' => $folderName,
                    'carpeta_padre_id' => $parentDbId,
                    'user_id' => Auth::id(),
                ]);
                
                // Crear la carpeta en OneDrive
                $onedriveFolder = $this->createFolderInOneDrive($folderName, $parentOnedriveId);
                
                if ($onedriveFolder) {
                    $newFolder->google_drive_folder_id = $onedriveFolder['id'];
                    $newFolder->save();
                    
                    // Disparar evento
                    event(new CarpetaCreada($newFolder));
                    
                    // Actualizar el mapa de carpetas
                    $folderMap[$folder['relative_path']] = [
                        'db_id' => $newFolder->id,
                        'onedrive_id' => $onedriveFolder['id']
                    ];
                }
            }
        }
        
        return $folderMap;
    }
    
    /**
     * Procesa los archivos en lotes para subirlos en paralelo
     */
    private function processFilesInBatches($files, $folderMap)
    {
        $totalProcessed = 0;
        $totalBatches = ceil(count($files) / $this->batchSize);
        
        // Procesar archivos en lotes
        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $batchFiles = array_slice($files, $batch * $this->batchSize, $this->batchSize);
            $processed = $this->uploadFileBatch($batchFiles, $folderMap);
            $totalProcessed += $processed;
            
            // Refrescar token cada 10 lotes para evitar expiración
            if ($batch > 0 && $batch % 10 === 0) {
                $this->refreshToken();
            }
        }
        
        return $totalProcessed;
    }
    
    /**
     * Sube un lote de archivos en paralelo
     */
    private function uploadFileBatch($files, $folderMap)
    {
        $promises = [];
        $dbRecords = [];
        
        foreach ($files as $fileInfo) {
            $folderPath = $fileInfo['folder_path'];
            
            // Verificar si existe la carpeta en el mapa
            if (!isset($folderMap[$folderPath])) {
                Log::warning("Carpeta no encontrada para archivo: {$fileInfo['name']} en {$folderPath}");
                continue;
            }
            
            $folderDbId = $folderMap[$folderPath]['db_id'];
            $folderOnedriveId = $folderMap[$folderPath]['onedrive_id'];
            
            // Verificar si el archivo ya existe en la base de datos
            $existingFile = Archivo::where('nombre', $fileInfo['name'])
                ->where('carpeta_id', $folderDbId)
                ->first();
            
            if ($existingFile) {
                continue; // Saltar archivos duplicados
            }
            
            // Crear registro en la base de datos
            $newFile = Archivo::create([
                'carpeta_id' => $folderDbId,
                'nombre' => $fileInfo['name'],
                'user_id' => Auth::id(),
                'mime_type' => $fileInfo['mime_type'],
            ]);
            
            $dbRecords[] = [
                'file_info' => $fileInfo,
                'record' => $newFile,
                'onedrive_folder_id' => $folderOnedriveId
            ];
            
            // Preparar promesa para subida asíncrona
            $promises[$newFile->id] = $this->prepareUploadPromise(
                $fileInfo['path'],
                $fileInfo['name'],
                $fileInfo['mime_type'],
                $folderOnedriveId
            );
        }
        
        // Ejecutar todas las promesas en paralelo
        $results = Promise\Utils::settle($promises)->wait();
        
        // Procesar resultados y actualizar registros
        foreach ($results as $fileId => $result) {
            $record = collect($dbRecords)->firstWhere('record.id', $fileId);
            
            if (!$record) continue;
            
            if ($result['state'] === 'fulfilled') {
                // Éxito en la subida
                $response = $result['value'];
                $uploadItem = json_decode($response->getBody()->getContents(), true);
                
                if (isset($uploadItem['id'])) {
                    // Obtener enlace de edición
                    $editLink = $this->getEditLink($uploadItem['id']);
                    
                    // Actualizar registro
                    $record['record']->google_drive_file_id = $uploadItem['id'];
                    $record['record']->edit_link = $editLink;
                    $record['record']->save();
                    
                    // Registrar la actividad
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'file_id' => $record['record']->id,
                        'action' => 'Ha Cargado',
                        'performed_at' => now(),
                    ]);
                    
                    event(new ArchivoCreado($record['record']));
                }
            } else {
                // Error en la subida
                Log::error('Error al subir archivo: ' . $result['reason']->getMessage(), [
                    'file' => $record['file_info']['name']
                ]);
                
                // Si es un error de token expirado, intentamos refrescar e intentar una vez más
                if (Str::contains($result['reason']->getMessage(), ['token', 'unauthorized', '401'])) {
                    $this->refreshToken();
                    
                    // Reintentar subida
                    try {
                        $response = $this->uploadFileToOneDrive(
                            $record['file_info']['path'],
                            $record['file_info']['name'],
                            $record['file_info']['mime_type'],
                            $record['onedrive_folder_id']
                        );
                        
                        $uploadItem = json_decode($response->getBody()->getContents(), true);
                        
                        if (isset($uploadItem['id'])) {
                            $editLink = $this->getEditLink($uploadItem['id']);
                            
                            $record['record']->google_drive_file_id = $uploadItem['id'];
                            $record['record']->edit_link = $editLink;
                            $record['record']->save();
                            
                            ActivityLog::create([
                                'user_id' => Auth::id(),
                                'file_id' => $record['record']->id,
                                'action' => 'Ha Cargado',
                                'performed_at' => now(),
                            ]);
                            
                            event(new ArchivoCreado($record['record']));
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al reintentar subida: ' . $e->getMessage());
                        $record['record']->delete(); // Eliminar registro fallido
                    }
                } else {
                    $record['record']->delete(); // Eliminar registro fallido
                }
            }
        }
        
        return count(array_filter($results, function($result) {
            return $result['state'] === 'fulfilled';
        }));
    }
    
    /**
     * Prepara una promesa para subir un archivo de forma asíncrona
     */
    private function prepareUploadPromise($filePath, $fileName, $mimeType, $parentFolderOnedriveId)
    {
        return $this->client->requestAsync('PUT', 
            "https://graph.microsoft.com/v1.0/me/drive/items/{$parentFolderOnedriveId}:/{$fileName}:/content",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => $mimeType,
                ],
                'body' => fopen($filePath, 'r')
            ]
        );
    }
    
    /**
     * Obtiene el enlace de edición para un archivo
     */
    private function getEditLink($fileId)
    {
        try {
            $response = $this->client->post(
                "https://graph.microsoft.com/v1.0/me/drive/items/{$fileId}/createLink",
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'type'  => 'edit',
                        'scope' => 'anonymous',
                    ],
                ]
            );
            
            $body = json_decode($response->getBody(), true);
            return $body['link']['webUrl'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error al obtener enlace de edición: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sube un archivo a OneDrive de forma síncrona
     */
    private function uploadFileToOneDrive($filePath, $fileName, $mimeType, $parentFolderOnedriveId)
    {
        return $this->client->put(
            "https://graph.microsoft.com/v1.0/me/drive/items/{$parentFolderOnedriveId}:/{$fileName}:/content",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => $mimeType,
                ],
                'body' => fopen($filePath, 'r')
            ]
        );
    }
    
    private function uploadLargeFileToOneDrive($filePath, $fileName, $mimeType, $parentFolderOnedriveId)
{
    $fileSize = filesize($filePath);
    $chunkSize = 10 * 1024 * 1024; // 10MB por chunk
    
    // 1. Crear una sesión de carga
    $response = $this->client->post(
        "https://graph.microsoft.com/v1.0/me/drive/items/{$parentFolderOnedriveId}:/{$fileName}:/createUploadSession",
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'item' => [
                    '@microsoft.graph.conflictBehavior' => 'rename',
                ]
            ]
        ]
    );
    
    $uploadSession = json_decode($response->getBody()->getContents(), true);
    $uploadUrl = $uploadSession['uploadUrl'];
    
    // 2. Subir el archivo por fragmentos
    $handle = fopen($filePath, 'r');
    $bytesRemaining = $fileSize;
    $bytesUploaded = 0;
    
    while ($bytesRemaining > 0) {
        $bytesToUpload = min($chunkSize, $bytesRemaining);
        
        // Leer fragmento
        $chunk = fread($handle, $bytesToUpload);
        
        // Calcular rangos
        $contentRange = "bytes {$bytesUploaded}-" . ($bytesUploaded + $bytesToUpload - 1) . "/{$fileSize}";
        
        // Subir fragmento
        $response = $this->client->put($uploadUrl, [
            'headers' => [
                'Content-Length' => $bytesToUpload,
                'Content-Range' => $contentRange,
            ],
            'body' => $chunk
        ]);
        
        $bytesRemaining -= $bytesToUpload;
        $bytesUploaded += $bytesToUpload;
        
        // Si es el último fragmento, obtener la respuesta completa
        if ($bytesRemaining === 0) {
            $uploadResult = json_decode($response->getBody()->getContents(), true);
            fclose($handle);
            return $uploadResult;
        }
    }
    
    fclose($handle);
    return null;
}
    
    /**
     * Crea una carpeta en OneDrive
     */
    private function createFolderInOneDrive($folderName, $parentFolderOnedriveId)
    {
        try {
            $endpoint = "https://graph.microsoft.com/v1.0/me/drive/items/{$parentFolderOnedriveId}/children";
            
            // Verificar si la carpeta ya existe
            $response = $this->client->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ]
            ]);
            
            $existingItems = json_decode($response->getBody()->getContents(), true);
            $existingFolder = collect($existingItems['value'])->firstWhere('name', $folderName);
            
            if ($existingFolder) {
                return $existingFolder;
            }
            
            // Crear la carpeta si no existe
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $folderName,
                    'folder' => new \stdClass(),
                    '@microsoft.graph.conflictBehavior' => 'rename',
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Error al crear carpeta en OneDrive: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Refresca el token de acceso
     */
    private function refreshToken()
    {
        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();
        $oneDriveToken = GoogleToken::find(1);
        $this->accessToken = $oneDriveToken->access_token;
    }


   public function compartir(Request $request)
    {
        // Validación de los datos recibidos
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permisos.ver' => 'nullable|boolean',
            'permisos.descargar' => 'nullable|boolean',
            'permisos.editar' => 'nullable|boolean',
            'permisos.eliminar' => 'nullable|boolean',
        ]);
    
        // Obtener la carpeta por el ID
        $carpeta = Carpeta::findOrFail($request->folder_id);
        $userId = $request->user_id;
        $permisos = $request->permisos;
    
        // Depuración: Mostrar los valores de la carpeta, el userId y los permisos
 
    
        // Asignar permisos a la carpeta principal
        $CarpetaPermiso = CarpetaPermiso::updateOrCreate(
            ['carpeta_id' => $carpeta->id, 'user_id' => $userId],
            [
                'ver' => isset($permisos['ver']) ? true : false,
                'descargar' => isset($permisos['descargar']) && $permisos['descargar'] == 1, // Aseguramos que se verifique correctamente
            ]
        );
    
       
        // Asignar permisos recursivamente a subcarpetas y archivos
        $this->asignarPermisosRecursivamente($carpeta, $userId, $permisos);
    
        return redirect()->route('file-system.home')->with('mensaje', 'Se ha compartido la Carpeta y su contenido con exito.')
            ->with('icono', 'success');
    }

    private function asignarPermisosRecursivamente($carpeta, $userId, $permisos)
    {
     
    
        // Asignar permisos a archivos dentro de la carpeta
        foreach ($carpeta->archivos as $archivo) {
            ArchivoPermiso::updateOrCreate(
                ['file_id' => $archivo->id, 'user_id' => $userId],
                [
                    'ver' => isset($permisos['ver']) ? true : false,
                    'descargar' => isset($permisos['descargar']) ? true : false,
                    'editar' => isset($permisos['editar']) ? true : false,
                    'eliminar' => isset($permisos['eliminar']) ? true : false,
                ]
            );
        }
    
        // Asignar permisos a subcarpetas
        foreach ($carpeta->carpetasHijas as $subcarpeta) {
            CarpetaPermiso::updateOrCreate(
                ['carpeta_id' => $subcarpeta->id, 'user_id' => $userId],
                [
                    'ver' => isset($permisos['ver']) ? true : false,
                    'descargar' => isset($permisos['descargar']) == true,
                ]
            );
    
          
            // Llamada recursiva para subcarpetas
            $this->asignarPermisosRecursivamente($subcarpeta, $userId, $permisos);
        }
    }
    
   public function createFile(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|in:docx,xlsx,pptx',
            'folder_id' => 'required|integer',
        ]);
    
        $fileName = $request->input('file_name');
        $fileType = $request->input('file_type');
        $folderId = $request->input('folder_id');
    
        // Obtener la carpeta desde la base de datos
        $folder = Carpeta::find($folderId);
        if (!$folder) {
            dd('Error: Carpeta no encontrada.');
            return redirect()->back()->with('error', 'Carpeta no encontrada.');
        }
    
        // Obtener el ID de la carpeta en OneDrive
        $parentFolderOnedriveId = $folder->google_drive_folder_id; // Asegúrate de tener este campo en tu modelo
    
        // Crear el archivo en OneDrive
        try {
            $uploadItem = $this->createFileInOneDrive($fileName, $fileType, $parentFolderOnedriveId);
    
            // Crear enlace de edición para el archivo subido
            $client = new Client();
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
            $oneDriveToken = GoogleToken::find(1);
            $accessToken = $oneDriveToken->access_token;
    
            $editLinkResponse = $client->post("https://graph.microsoft.com/v1.0/me/drive/items/{$uploadItem['id']}/createLink", [
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
    
            // Guardar la información del archivo en la base de datos
            $newFile = new Archivo();
            $newFile->nombre = $fileName . '.' . $fileType;
            $newFile->google_drive_file_id = $uploadItem['id'];
            $newFile->carpeta_id = $folderId;
            $newFile->user_id = Auth::id();
            $newFile->edit_link = $editLink; // Guardar el enlace de edición
            $newFile->save();
    
            // Registrar la actividad en el historial
            ActivityLog::create([
                'user_id' => Auth::id(),
                'file_id' => $newFile->id,
                'action' => 'Ha Cargado',
                'performed_at' => now(),
            ]);
    
            event(new ArchivoCreado($newFile));
    
            return redirect()->back()->with('mensaje', 'Se ha creado el documento.')
                ->with('icono', 'success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el archivo: ' . $e->getMessage());
        }
    }
    
    private function createFileInOneDrive($fileName, $fileType, $parentFolderOnedriveId)
    {
        $GoogleAuthController = new GoogleAuthController();
        $GoogleAuthController->refreshAccessToken();
        $oneDriveToken = GoogleToken::find(1);
        $accessToken = $oneDriveToken->access_token;
    
  
    
        // Determinar la extensión y el tipo de contenido basado en el tipo de archivo
        switch ($fileType) {
            case 'docx':
                $extension = '.docx';
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                $templateContent = file_get_contents(storage_path('app/templates/blank.docx'));
                break;
            case 'xlsx':
                $extension = '.xlsx';
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                $templateContent = file_get_contents(storage_path('app/templates/blank.xlsx'));
                break;
            case 'pptx':
                $extension = '.pptx';
                $contentType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                $templateContent = file_get_contents(storage_path('app/templates/blank.pptx'));
                break;
            default:
                $extension = '';
                $contentType = 'application/octet-stream';
                $templateContent = '';
                break;
        }
    
      
    
        $fullFileName = $fileName . $extension;
    
        $client = new Client();
        $endpoint = 'https://graph.microsoft.com/v1.0/me/drive/items/' . $parentFolderOnedriveId . ':/' . $fullFileName . ':/content';
    
        // Crear el archivo en OneDrive
        $response = $client->put($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => $contentType,
                'Accept' => '*/*',
            ],
            'body' => $templateContent, // Enviamos el contenido del archivo de plantilla
        ]);
    
 
    
        // Obtener la respuesta y devolverla
        $uploadItem = json_decode($response->getBody()->getContents(), true);
   
    
        return $uploadItem;
    }
    
    
 
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        // Buscar carpetas y archivos que coincidan con el término
        $folders = Carpeta::with(['permisos', 'carpetasHijas'])
            ->where('nombre', 'LIKE', '%' . $query . '%')
            ->get();
    
        $files = Archivo::with(['permisos', 'carpeta'])
            ->where('nombre', 'LIKE', '%' . $query . '%')
            ->get();
            
            // Retornar una respuesta JSON con los resultados
            return response()->json([
                'folders' => $folders,
                'files' => $files,
            ]);
    }
    
public function moverArchivo(Request $request)
{
    // Log de entrada al método
    Log::info('Iniciando moverArchivo con datos del request', $request->all());

    // Asegúrate de que los datos del archivo y la carpeta de destino estén presentes
    $archivoId = $request->archivoId;
    $carpetaDestinoId = $request->carpetaDestinoId;

    // Log de los valores recibidos
    Log::info('Valores recibidos del formulario', [
        'archivoId' => $archivoId,
        'carpetaDestinoId' => $carpetaDestinoId,
    ]);

    // Verifica que estos valores no sean nulos antes de continuar
    if ($archivoId && $carpetaDestinoId) {
        $archivo = Archivo::find($archivoId);
        $carpetaDestino = Carpeta::find($carpetaDestinoId);

        // Log de objetos encontrados
        Log::info('Objeto archivo encontrado', ['archivo' => $archivo]);
        Log::info('Objeto carpeta destino encontrado', ['carpetaDestino' => $carpetaDestino]);

        // Verifica que los objetos no sean nulos
        if (!$archivo || !$carpetaDestino) {
            Log::error('Archivo o carpeta no encontrados.', [
                'archivoId' => $archivoId,
                'carpetaDestinoId' => $carpetaDestinoId,
            ]);
            return response()->json(['error' => 'Archivo o carpeta destino no encontrados.'], 404);
        }

        // Llamar a la función para mover el archivo
        try {
            $moved = $this->moveFileToOneDrive($archivo, $carpetaDestino->google_drive_folder_id);

            // Log del resultado del movimiento
            Log::info('Resultado del movimiento del archivo', ['moved' => $moved]);

            if ($moved) {
                // Actualizar carpeta_padre_id en la base de datos
                $archivo->carpeta_id = $carpetaDestinoId;
                $archivo->save();

                Log::info('Archivo movido exitosamente y actualizado en la base de datos.');
               return response()->json([
                    'success' => true,
                    'message' => 'Archivo movido correctamente'
                ], 200);
                
                                
    
            } else {
                Log::error('Error al mover el archivo.');
                return response()->json(['error' => 'Error al mover el archivo.'], 500);
            }
        } catch (\Exception $e) {
            // Log de cualquier excepción
            Log::error('Excepción durante el movimiento del archivo', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al procesar la solicitud.'], 500);
        }
    }

    // Log de datos incompletos
    Log::warning('Datos incompletos en la solicitud.', [
        'archivoId' => $archivoId,
        'carpetaDestinoId' => $carpetaDestinoId,
    ]);
    return response()->json(['error' => 'Datos incompletos.'], 400);
}
    private function moveFileToOneDrive($archivo, $parentFolderOnedriveId)
    {
        Log::info('Iniciando moveFileToOneDrive', [
            'archivo' => $archivo->toArray(),
            'parentFolderOnedriveId' => $parentFolderOnedriveId,
        ]);
    
        try {
            // Obtén el token de acceso para OneDrive
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
    
            $oneDriveToken = GoogleToken::find(1);
            $accessToken = $oneDriveToken->access_token;
    
            Log::info('Token de OneDrive obtenido', ['accessToken' => $accessToken]);
    
            // Obtén el ID de OneDrive del archivo
            $onedriveFileId = $archivo->google_drive_file_id;
            Log::info('ID de OneDrive del archivo', ['onedriveFileId' => $onedriveFileId]);
            
            $updatedMetadata = [
                'parentReference' => [
                    'id' => $parentFolderOnedriveId, // ID de la carpeta destino en OneDrive
                ],
                'name' => $archivo->nombre, // Opcional: Cambiar el nombre del archivo al moverlo
            ];
    
            // Realizar la solicitud PATCH a la API de Microsoft Graph
            $response = Http::withToken($accessToken)
                ->patch("https://graph.microsoft.com/v1.0/me/drive/items/{$archivo->google_drive_file_id}", $updatedMetadata);
    
            // Log de la respuesta de Microsoft Graph
            $moveItem = json_decode($response->getBody()->getContents(), true);
            Log::info('Respuesta de Microsoft Graph', ['response' => $moveItem]);
    
            if ($response->successful()) {
                $moveItem = $response->json();
                Log::info('Respuesta de Microsoft Graph:', ['response' => $moveItem]);
    
                // Actualiza el ID del archivo en la base de datos si es necesario
                $archivo->google_drive_file_id = $moveItem['id'] ?? $archivo->google_drive_file_id;
                $archivo->save();
    
                Log::info('Archivo movido y actualizado en la base de datos.');
                return true;
            }
    
            Log::warning('No se pudo mover el archivo en OneDrive.');
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción en moveFileToOneDrive', ['exception' => $e->getMessage()]);
            return false;
        }
    }
    
    public function moverCarpeta(Request $request)
    {
        Log::info('Iniciando moverCarpeta con datos del request', $request->all());
    
        $carpetaId = $request->folderId;
        $carpetaDestinoId = $request->carpetaDestino;
    
        Log::info('Valores recibidos del formulario', [
            'carpetaId' => $carpetaId,
            'carpetaDestinoId' => $carpetaDestinoId,
        ]);
    
        if ($carpetaId) {
            $carpeta = Carpeta::find($carpetaId);
    
            if (!$carpeta) {
                Log::error('Carpeta no encontrada.', ['carpetaId' => $carpetaId]);
                return redirect()->back()->with('mensaje', 'Carpeta no encontrada.')->with('icono', 'error');
            }
    
            try {
                if ($carpetaDestinoId) {
                    // Si hay una carpeta destino, mover la carpeta en OneDrive
                    $carpetaDestino = Carpeta::find($carpetaDestinoId);
    
                    if (!$carpetaDestino) {
                        Log::error('Carpeta destino no encontrada.', ['carpetaDestinoId' => $carpetaDestinoId]);
                        return redirect()->back()->with('mensaje', 'Carpeta destino no encontrada.')->with('icono', 'error');
                    }
    
                    $moved = $this->moveFolderToOneDrive($carpeta, $carpetaDestino->google_drive_folder_id);
    
                    if (!$moved) {
                        Log::error('Error al mover la carpeta en OneDrive.');
                        return redirect()->back()->with('mensaje', 'Hubo un error al intentar trasladar la carpeta.')->with('icono', 'error');
                    }
    
                    $carpeta->carpeta_padre_id = $carpetaDestinoId;
                } else {
                    // Si no hay carpeta destino, mover al nivel raíz
                    $carpeta->carpeta_padre_id = null;
                    Log::info('Carpeta movida al nivel raíz.');
                }
    
                // Guardar los cambios en la base de datos
                $carpeta->save();
    
                Log::info('Carpeta movida exitosamente y actualizada en la base de datos.');
                return redirect()->back()->with('mensaje', 'Carpeta movida correctamente.')->with('icono', 'success');
            } catch (\Exception $e) {
                Log::error('Excepción durante el movimiento de la carpeta', ['exception' => $e->getMessage()]);
                return redirect()->back()->with('mensaje', 'Ocurrió un error al procesar la solicitud.')->with('icono', 'error');
            }
        }
    
        Log::warning('Datos incompletos en la solicitud.', [
            'carpetaId' => $carpetaId,
            'carpetaDestinoId' => $carpetaDestinoId,
        ]);
    
        return redirect()->back()->with('mensaje', 'Datos incompletos.')->with('icono', 'error');
    }
    
    private function moveFolderToOneDrive($carpeta, $parentFolderOnedriveId)
    {
        Log::info('Iniciando moveFolderToOneDrive', [
            'carpeta' => $carpeta->toArray(),
            'parentFolderOnedriveId' => $parentFolderOnedriveId,
        ]);
    
        try {
            $GoogleAuthController = new GoogleAuthController();
            $GoogleAuthController->refreshAccessToken();
    
            $oneDriveToken = GoogleToken::find(1);
            $accessToken = $oneDriveToken->access_token;
    
            Log::info('Token de OneDrive obtenido', ['accessToken' => $accessToken]);
    
            $onedriveFolderId = $carpeta->google_drive_folder_id;
            Log::info('ID de OneDrive de la carpeta', ['onedriveFolderId' => $onedriveFolderId]);
    
            $updatedMetadata = [
                'parentReference' => [
                    'id' => $parentFolderOnedriveId,
                ],
                'name' => $carpeta->nombre,
            ];
    
            $response = Http::withToken($accessToken)
                ->patch("https://graph.microsoft.com/v1.0/me/drive/items/{$onedriveFolderId}", $updatedMetadata);
    
            $moveItem = json_decode($response->getBody()->getContents(), true);
            Log::info('Respuesta de Microsoft Graph', ['response' => $moveItem]);
    
            if ($response->successful()) {
                $moveItem = $response->json();
                Log::info('Respuesta de Microsoft Graph:', ['response' => $moveItem]);
    
                $carpeta->google_drive_folder_id = $moveItem['id'] ?? $carpeta->google_drive_folder_id;
                $carpeta->save();
    
                Log::info('Carpeta movida y actualizada en la base de datos.');
                return true;
            }
    
            Log::warning('No se pudo mover la carpeta en OneDrive.');
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción en moveFolderToOneDrive', ['exception' => $e->getMessage()]);
            return false;
        }
    }
        
    
}

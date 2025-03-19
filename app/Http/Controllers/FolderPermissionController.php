<?php

namespace App\Http\Controllers;
use App\Models\CarpetaPermiso;
use App\Models\ArchivoPermiso;
use App\Models\Carpeta;
use App\Models\User;
use Illuminate\Http\Request;

class FolderPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $folders = Carpeta::with([
            'carpetasHijas',
            'archivos.permisos' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
            'permisos'
        ])->whereNull('carpeta_padre_id')->get();

        return view('admin.users.folder_file.permissions', compact('folders', 'user'));
    }

    public function guardarPermisos(Request $request, $userId)
    {
        // Validar la solicitud
        $validated = $request->validate([
            'permisos.file' => 'array',
            'permisos.carpeta' => 'array',
        ]);
    
        \Log::info('Guardar permisos para el usuario ID: ' . $userId);
    
        // Procesar permisos de archivos
        if (isset($request->permisos['file'])) {
            foreach ($request->permisos['file'] as $fileId => $permissions) {
                ArchivoPermiso::updateOrCreate(
                    ['user_id' => $userId, 'file_id' => $fileId],
                    [
                        'ver' => isset($permissions['ver']),
                        'descargar' => isset($permissions['descargar']),
                        'editar' => isset($permissions['editar']),
                        'eliminar' => isset($permissions['eliminar']),
                    ]
                );
                \Log::info("Permisos actualizados para Archivo ID: {$fileId}");
            }
        }
    
        // Procesar permisos de carpetas
        if (isset($request->permisos['carpeta'])) {
            foreach ($request->permisos['carpeta'] as $folderId => $permissions) {
                CarpetaPermiso::updateOrCreate(
                    ['user_id' => $userId, 'carpeta_id' => $folderId],
                    [
                        'ver' => isset($permissions['ver']),
                        'descargar' => isset($permissions['descargar']),
                    ]
                );
                \Log::info("Permisos actualizados para Carpeta ID: {$folderId}");
            }
        }
    
        // Revocar permisos de carpetas que no están presentes en la solicitud
        // Obtener todas las carpetas que tienen permisos para el usuario
        $carpetasConPermisos = CarpetaPermiso::where('user_id', $userId)->pluck('carpeta_id')->toArray();
    
        // Carpetas incluidas en la solicitud
        $carpetasSolicitadas = isset($request->permisos['carpeta']) ? array_keys($request->permisos['carpeta']) : [];
    
        // Carpetas a revocar permisos
        $carpetasARevocar = array_diff($carpetasConPermisos, $carpetasSolicitadas);
    
        if (!empty($carpetasARevocar)) {
            CarpetaPermiso::where('user_id', $userId)
                ->whereIn('carpeta_id', $carpetasARevocar)
                ->delete();
    
            \Log::info("Permisos revocados para Carpetas IDs: " . implode(',', $carpetasARevocar));
        }
    
        // Redirigir con mensaje de éxito
        return redirect()->back()
            ->with('mensaje', 'Permisos actualizados correctamente.')
            ->with('icono', 'success');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carpeta;
use App\Models\Archivo;
use App\Models\User;
use App\Models\CarpetaPermiso;
use App\Models\ArchivoPermiso;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Otorga permisos a una carpeta.
     */
    public function grantFolderPermission(Request $request)
    {
        $request->validate([
            'carpeta_id' => 'required|exists:carpetas,id', // Cambiado de 'folder_id' a 'carpeta_id'
            'user_id' => 'required|exists:users,id',
            'ver' => 'sometimes|boolean', // Cambiado de 'can_view' a 'ver'
            'descargar' => 'sometimes|boolean', // Cambiado de 'can_edit' a 'descargar' si es aplicable
        ]);

        CarpetaPermiso::updateOrCreate(
            ['carpeta_id' => $request->carpeta_id, 'user_id' => $request->user_id],
            ['ver' => $request->ver, 'descargar' => $request->descargar]
        );

        return redirect()->back()->with('success', 'Permiso otorgado correctamente.');
    }

    /**
     * Revoca permisos de una carpeta.
     */
    public function revokeFolderPermission(Request $request)
    {
        $request->validate([
            'carpeta_id' => 'required|exists:carpetas,id', // Cambiado de 'folder_id' a 'carpeta_id'
            'user_id' => 'required|exists:users,id',
        ]);

        $permission = CarpetaPermiso::where('carpeta_id', $request->carpeta_id) // Cambiado de 'folder_id' a 'carpeta_id'
            ->where('user_id', $request->user_id)
            ->first();

        if ($permission) {
            $permission->delete();
        }

        return redirect()->back()->with('success', 'Permiso revocado correctamente.');
    }

    /**
     * Otorga permisos a un archivo.
     */
    public function grantFilePermission(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:archivos,id',
            'user_id' => 'required|exists:users,id',
            'ver' => 'sometimes|boolean', // Cambiado de 'can_view' a 'ver'
            'descargar' => 'sometimes|boolean', // 'descargar'
            'editar' => 'sometimes|boolean', // 'editar'
            'eliminar' => 'sometimes|boolean', // 'eliminar'
        ]);

        ArchivoPermiso::updateOrCreate(
            ['file_id' => $request->file_id, 'user_id' => $request->user_id],
            [
                'ver' => $request->ver,
                'descargar' => $request->descargar,
                'editar' => $request->editar,
                'eliminar' => $request->eliminar,
            ]
        );

        return redirect()->back()->with('success', 'Permiso otorgado correctamente.');
    }

    /**
     * Revoca permisos de un archivo.
     */
    public function revokeFilePermission(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:archivos,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $permission = ArchivoPermiso::where('file_id', $request->file_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($permission) {
            $permission->update([
                'ver' => false,
                'descargar' => false,
                'editar' => false,
                'eliminar' => false,
            ]);
        }

        return redirect()->back()->with('success', 'Permiso revocado correctamente.');
    }

    /**
     * Muestra la vista de permisos.
     */
    public function index()
    {
        $carpetas = Carpeta::all(); // Obtener todas las carpetas
        $archivos = Archivo::all(); // Obtener todos los archivos
        $users = User::all(); // Obtener todos los usuarios

        return view('admin.permissions', compact('carpetas', 'archivos', 'users'));
    }
}

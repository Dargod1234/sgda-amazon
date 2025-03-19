<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Carpeta;
use App\Models\Archivo;

class UnidadController extends Controller
{
    public function index($id)
    {
        $user = User::findOrFail($id);
    
        // Obtener las carpetas del usuario con permisos
        $carpetas = Carpeta::with(['permisos' => function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('ver', true); // Asegurarse de que tiene permiso de ver
        }])
        ->whereNull('carpeta_padre_id')
        ->whereHas('permisos', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('ver', true); // Solo obtener carpetas donde tiene permiso de ver
        })
        ->get();
    
        $archivos = []; // Creamos un array vacío para los archivos
        $subcarpetas = []; // Creamos un array vacío para las subcarpetas
    
        // Iteramos sobre las carpetas del usuario
        foreach ($carpetas as $carpeta) {
            // Obtenemos las subcarpetas asociadas a esta carpeta
            $subcarpetas[$carpeta->id] = Carpeta::where('carpeta_padre_id', $carpeta->id)
                ->whereHas('permisos', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('ver', true); // Solo obtener subcarpetas donde tiene permiso de ver
                })
                ->get();
    
            // Obtenemos los archivos asociados a esta carpeta
            $archivos[$carpeta->id] = $carpeta->archivos()->whereHas('permisos', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('ver', true); // Solo obtener archivos donde tiene permiso de ver
            })->get();
        }
    
        return view('admin.unidad', compact('user', 'carpetas', 'subcarpetas', 'archivos', 'id'));
    }
    



}

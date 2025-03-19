<?php

namespace App\Listeners;

use App\Events\CarpetaCreada;
use App\Models\CarpetaPermiso;
use App\Models\User;

class CrearPermisoParaCarpeta
{
    public function handle(CarpetaCreada $event)
    {
        $carpeta = $event->carpeta;
        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            // Comprobar si el usuario es el creador o el administrador
            $esCreador = (int)$usuario->id === (int)$carpeta->user_id;
            $esAdministrador = (int)$usuario->id === 1; // Asumiendo que tienes un campo `is_admin` en tu modelo User
            CarpetaPermiso::create([
                'user_id' => $usuario->id,
                'carpeta_id' => $carpeta->id,
                'ver' => $esCreador || $esAdministrador, // Permitir ver solo si es el creador o administrador
                'descargar' => true || $esAdministrador,
            ]);
        }
    }
}

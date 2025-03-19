<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ArchivoCreado;
use App\Models\ArchivoPermiso;
use App\Models\User;
class CrearPermisoParaArchivo
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ArchivoCreado $event)
    {
        $archivo = $event->archivo;
        $usuarios = User::all();

        foreach ($usuarios as $usuario) {
            // Comprobar si el usuario es el creador o el administrador
            $esCreador = $usuario->id === $archivo->user_id;
            $esAdministrador = $usuario->id === 1; // Asumiendo que tienes un campo `is_admin` en tu modelo User
            ArchivoPermiso::create([
                'user_id' => $usuario->id,
                'file_id' => $archivo->id,
                'descargar' => $esCreador || $esAdministrador,
                'editar' => $esCreador || $esAdministrador,
                'eliminar' => $esCreador || $esAdministrador,
                'ver' => $esCreador || $esAdministrador, // Permitir ver solo si es el creador o administrador
            ]);
        }
    }

}

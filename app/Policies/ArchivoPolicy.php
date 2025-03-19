<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Archivo;

class ArchivoPolicy
{
    /**
     * Determine if the given user can view the given archivo.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Archivo  $archivo
     * @return bool
     */
    public function view(User $user, Archivo $archivo)
    {
        // Permitir al admin ver todos los archivos
        if ($user->hasRole('admin')) {
            return true;
        }

        // Permitir al usuario ver archivos en sus propias carpetas
        return $archivo->carpeta->user_id === $user->id;
    }

    /**
     * Determine if the given user can delete the given archivo.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Archivo  $archivo
     * @return bool
     */
    public function delete(User $user, Archivo $archivo)
    {
        // Permitir al admin eliminar todos los archivos
        if ($user->hasRole('admin')) {
            return true;
        }

        // Permitir al usuario eliminar archivos en sus propias carpetas
        return $archivo->carpeta->user_id === $user->id;
    }
}

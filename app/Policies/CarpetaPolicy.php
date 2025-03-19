<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Carpeta;

class CarpetaPolicy
{
    /**
     * Determine if the given user can view the given carpeta.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Carpeta  $carpeta
     * @return bool
     */
    public function view(User $user, Carpeta $carpeta)
    {
        // Permitir al admin ver todas las carpetas
        if ($user->hasRole('admin')) {
            return true;
        }

        // Permitir al usuario ver solo sus propias carpetas
        if ($carpeta->user_id === $user->id) {
            return true;
        }

        // Verificar si el usuario tiene permisos específicos para ver la carpeta
        return $carpeta->usuariosConPermiso()->where('user_id', $user->id)->where('can_view', true)->exists();
    }
}

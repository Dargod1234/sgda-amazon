<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoPermiso extends Model
{
    use HasFactory;

    protected $table = 'archivo_permisos';

    // Definir los campos que se pueden asignar masivamente (mass-assignment)
    protected $fillable = [
        'user_id',
        'file_id',
        'ver',
        'descargar',
        'editar',
        'eliminar',
    ];

    /**
     * Relación con el modelo User.
     * Un permiso pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el modelo Archivo.
     * Un permiso pertenece a un archivo.
     */
    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }
}


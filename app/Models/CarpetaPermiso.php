<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarpetaPermiso extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'carpeta_id',
        'ver',
        'descargar'
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Definir la relación con el modelo Carpeta
    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activos extends Model
{
    protected $fillable = [
        'client_id',
        'nombre',
        'descripcion',
        'cantidad',
        'fecha_expiracion'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
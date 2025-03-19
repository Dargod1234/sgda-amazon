<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archivo;
use App\Models\User;
use Carbon\Carbon; // Asegúrate de importar Carbon

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_id', 'action', 'performed_at'];

    // Definir la fecha como una instancia de Carbon
    protected $casts = ['performed_at' => 'datetime',];


    public function user() {
        return $this->belongsTo(User::class); // Relación existente con User
    }

    public function file() {
        return $this->belongsTo(Archivo::class); // Relación existente con File
    }
}

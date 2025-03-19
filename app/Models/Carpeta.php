<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class Carpeta extends Model
{

    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['nombre', 'carpeta_padre_id', 'user_id', 'google_drive_folder_id', 'color'];
    // Si la tabla se llama 'folders', asegúrate de especificarlo
    protected $table = 'carpetas';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($carpeta) {
            if (Auth::check()) {
                $carpeta->deleted_by = Auth::id();
                $carpeta->save();
            }
        });
    }


    public function marcarComoObsoleta()
    {
        $this->estado = false; // Cambiar el estado a obsoleto
        $this->save();
    }

    public function marcarComoActiva()
    {
        $this->estado = true; // Cambiar el estado a activa
        $this->save();
    }

    public static function obtenerObsoletas()
    {
        return self::where('estado', false)->get();
    }
  

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeMovidoPor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function carpetasHijas(){
        return $this->hasMany(Carpeta::class, 'carpeta_padre_id');
    }

    // Relación uno a muchos con Archivos
    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    // Relación con el Usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function permisos()
    {
        return $this->hasMany(CarpetaPermiso::class);
    }
    // Relación muchos a muchos con usuarios para permisos de carpetas
    public function usuariosConPermiso()
    {
        return $this->belongsToMany(User::class, 'carpeta_permisos', 'carpeta_id',  'user_id')
            ->withPivot('ver', 'descargar');
    }

  

    // Método para verificar si un usuario tiene permisos para una carpeta
    public function tienePermiso($userId, $permiso)
    {
        return $this->usuariosConPermiso()
            ->wherePivot('user_id', $userId)
            ->wherePivot($permiso, true)
            ->exists();
    }

}

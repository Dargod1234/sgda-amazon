<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class Archivo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['nombre', 'carpeta_id', 'user_id', 'edit_link'];


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($archivo) {
            if (Auth::check()) {
                $archivo->deleted_by = Auth::id();
                $archivo->save();
            }
        });
    }
    public function carpeta(){
        return $this->belongsTo(Carpeta::class);
    }

    public function permisos()
    {
        return $this->hasMany(ArchivoPermiso::class, 'file_id');
    }

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * RelaciÃ³n inversa, para obtener las carpetas movidas por un usuario.
     */
    public function scopeMovidoPor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
     // Relaci¨®n con el Usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function tienePermisoVer($userId)
    {
        return $this->permisos()
            ->where('user_id', $userId)
            ->where('ver', true)
            ->exists();
    }

}

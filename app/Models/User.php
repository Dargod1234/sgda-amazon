<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    public function archivosMovidos()
    {
        return $this->hasMany(Archivo::class, 'user_dropper_id');
    }

    /**
     * Relación con las carpetas que este usuario ha movido a la papelera.
     */
    public function carpetasMovidas()
    {
        return $this->hasMany(Carpeta::class, 'user_dropper_id');
    }

    public function carpetas()
    {
        return $this->hasMany(Carpeta::class)->withoutTrashed();
    }
    
    public function archivos()
    {
        return $this->hasMany(Archivo::class)->withoutTrashed();
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }


    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'legal_representative_phone', // Teléfono cliente
        'business_name', // Nombre de la empresa/copropiedad
        'legal_representative_name', // Nombre del representante legal
        'legal_representative_email', // NIT/CC del representante legal
        'email',
        'phone',
        'address',
        'nit_cc',
        'contract_owner',
        'contract_start',
        'contract_end',
        'notes' // Nuevo campo
    ];

    protected $casts = [
        'contract_start' => 'date',
        'contract_end' => 'date',
    ];

    public function isNearExpiration(): bool
    {
        return $this->contract_end->diffInDays(now()) <= config('alertas.dias_alerta');
    }
    
    public function activos()
    {
        return $this->hasMany(Activos::class, 'client_id');
    }

    public function isAboutToExpire(): bool
    {
        return $this->contract_end->diffInDays(now()) <= config('alertas.dias_urgente');
    }
    
        public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function daysUntilExpiration(): int
    {
        return now()->diffInDays($this->contract_end, false);
    }

    // Scopes para consultas
    public function scopeNearExpiration($query)
    {
        return $query->where('contract_end', '<=', now()->addDays((int) config('alertas.dias_alerta')));
    }

    public function scopeAboutToExpire($query)
    {
        return $query->where('contract_end', '<=', now()->addDays((int) config('alertas.dias_urgente')));
    }

    public function scopeActiveAlerts($query)
    {
        return $query->where(function ($q) {
            $q->aboutToExpire()
                ->orWhere(function ($query) {
                    $query->nearExpiration();
                });
        });
    }
}
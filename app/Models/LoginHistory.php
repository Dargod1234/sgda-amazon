<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'city',
        'region',
        'country',
        'loc',
        'org',
        'postal',
        'timezone',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

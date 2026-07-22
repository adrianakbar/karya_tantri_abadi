<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cooperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'chairman_name',
        'established_date',
        'logo_url',
        'theme_color',
        'is_active',
        'subscription_expired_at',
    ];

    protected $casts = [
        'established_date' => 'date',
        'subscription_expired_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}

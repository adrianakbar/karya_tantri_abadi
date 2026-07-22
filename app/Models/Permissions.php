<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permissions extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module',
        'description',
    ];
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Roles::class, 'role_permissions', 'permission_id', 'role_id');
    }
}

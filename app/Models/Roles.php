<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'cooperation_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cooperation(): BelongsTo
    {
        return $this->belongsTo(Cooperation::class, 'cooperation_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permissions::class, 'role_permissions', 'role_id', 'permission_id');
    }
}

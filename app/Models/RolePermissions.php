<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
    public $incrementing = true;

    protected $table = 'role_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];
}

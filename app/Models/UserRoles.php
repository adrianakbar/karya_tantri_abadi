<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRoles extends Pivot
{
    public $incrementing = true;

    protected $table = 'user_roles';
}

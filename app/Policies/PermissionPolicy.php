<?php

namespace App\Policies;

use App\Models\Permissions;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_permissions']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permissions $permission): bool
    {
        return $user->hasAnyPermission(['view_permissions']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['manage_permissions']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permissions $permission): bool
    {
        return $user->hasAnyPermission(['manage_permissions']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permissions $permission): bool
    {
        return $user->hasAnyPermission(['manage_permissions']);
    }
}

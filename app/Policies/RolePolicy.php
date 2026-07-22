<?php

namespace App\Policies;

use App\Models\Roles;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_roles']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Roles $role): bool
    {
        return $user->hasAnyPermission(['view_roles']) &&
            $role->cooperation_id === $user->cooperation_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create_roles']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Roles $role): bool
    {
        // Only admin can edit admin role
        if ($role->name === 'admin' && !$user->hasRole('admin')) {
            return false;
        }

        return $user->hasAnyPermission(['edit_roles']) &&
            $role->cooperation_id === $user->cooperation_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Roles $role): bool
    {
        // Cannot delete admin role
        if ($role->name === 'admin') {
            return false;
        }

        return $user->hasAnyPermission(['delete_roles']) &&
            $role->cooperation_id === $user->cooperation_id;
    }
}

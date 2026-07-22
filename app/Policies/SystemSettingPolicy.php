<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view_settings']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SystemSetting $systemSetting): bool
    {
        return $user->hasAnyPermission(['view_settings']) &&
            $systemSetting->cooperation_id === $user->cooperation_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['manage_settings']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SystemSetting $systemSetting): bool
    {
        if ($systemSetting->is_system && !$user->hasRole('admin')) {
            return false;
        }

        return $user->hasAnyPermission(['manage_settings']) &&
            $systemSetting->cooperation_id === $user->cooperation_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SystemSetting $systemSetting): bool
    {
        if ($systemSetting->is_system) {
            return false;
        }

        return $user->hasAnyPermission(['manage_settings']) &&
            $systemSetting->cooperation_id === $user->cooperation_id;
    }
}

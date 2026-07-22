<?php

namespace App\Providers;

use App\Models\Permissions;
use App\Models\Roles;
use App\Models\SystemSetting;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SystemSetting::class => SystemSettingPolicy::class,
        Roles::class => RolePolicy::class,
        Permissions::class => PermissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Default admin can do everything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}

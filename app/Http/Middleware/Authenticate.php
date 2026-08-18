<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use App\Models\UserRole;
use App\Models\Roles;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends BaseAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        $user = $guard->user();
        $panel = Filament::getCurrentPanel();

        // Check if user is allowed to access this panel
        $canAccess = $user instanceof FilamentUser ?
            $user->canAccessPanel($panel) :
            (config('app.env') === 'local');

        if (! $canAccess) {
            // User cannot access this panel. Determine redirect based on role
            $userRole = UserRole::where('user_id', $user->id)->first();
            if ($userRole) {
                $role = Roles::find($userRole->role_id);
                if ($role) {
                    $redirectUrl = match ($role->name) {
                        'admin', 'manager' => '/admin',
                        'anggota' => '/anggota',
                        'petugas' => '/petugas',
                        'kasir', 'cashier', 'bendahara' => '/kasir',
                        'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                        default => null,
                    };

                    if ($redirectUrl) {
                        // Prevent redirect loop if the user is already on the target path
                        $currentPath = trim($request->getPathInfo(), '/');
                        $targetPath = trim($redirectUrl, '/');
                        
                        if ($currentPath !== $targetPath) {
                            throw new HttpResponseException(redirect($redirectUrl));
                        }
                    }
                }
            }

            abort(403);
        }
    }
}

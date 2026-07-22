<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRole;
use App\Models\Roles;
use Illuminate\Support\Facades\Log;

class RedirectBasedOnRole
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply to authenticated users on admin panel routes
        if (Auth::check() && $request->is('admin*')) {
            $user = Auth::user();
            $userRole = UserRole::where('user_id', $user->id)->first();

            if ($userRole) {
                $role = Roles::find($userRole->role_id);

                if ($role && !in_array($role->name, ['admin', 'manager'], true)) {
                    $redirectUrl = match ($role->name) {
                        'anggota' => '/anggota',
                        'kasir', 'cashier', 'bendahara' => '/kasir',
                        'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                        default => '/auth/login',
                    };

                    Log::info('RedirectBasedOnRole: Redirecting non-admin user', [
                        'user_id' => $user->id,
                        'role' => $role->name,
                        'redirect_to' => $redirectUrl,
                    ]);

                    return redirect($redirectUrl);
                }
            }
        }
        
        return $next($request);
    }
}
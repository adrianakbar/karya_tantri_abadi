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
        $u = Auth::user();
        $ur = $u ? UserRole::where('user_id', $u->id)->first() : null;
        $r = $ur ? Roles::find($ur->role_id) : null;
        echo "MIDDLEWARE EXEC: Path=" . $request->path() . ", User=" . ($u ? $u->email : 'GUEST') . ", Role=" . ($r ? $r->name : 'NONE') . "\n";
        // Only apply to authenticated users on admin panel routes
        if (Auth::check() && $request->is('admin*')) {
            $user = Auth::user();
            $userRole = UserRole::where('user_id', $user->id)->first();
            
            Log::info('RedirectBasedOnRole: Processing admin route access', [
                'user_id' => $user->id,
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
                'userRole_exists' => $userRole ? 'YES' : 'NO'
            ]);
            
            if ($userRole) {
                $role = Roles::find($userRole->role_id);
                
                Log::info('RedirectBasedOnRole: User role check', [
                    'user_id' => $user->id,
                    'role_name' => $role?->name,
                    'is_admin' => $role && $role->name === 'admin' ? 'YES' : 'NO'
                ]);
                
                if ($role && $role->name !== 'admin') {
                    // Non-admin user trying to access admin routes
                    $redirectUrl = match ($role->name) {
                        'anggota', 'petugas' => '/anggota',
                        'kasir' => '/kasir',
                        'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                        default => '/anggota'
                    };
                    
                    Log::info('RedirectBasedOnRole: Redirecting non-admin user', [
                        'user_id' => $user->id,
                        'role' => $role->name,
                        'redirect_to' => $redirectUrl
                    ]);
                    
                    return redirect($redirectUrl);
                } else if ($role && $role->name === 'admin') {
                    // Admin user - allow access
                    Log::info('RedirectBasedOnRole: Admin user allowed access', [
                        'user_id' => $user->id,
                        'role' => $role->name
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}
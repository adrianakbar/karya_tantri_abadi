<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRole;
use App\Models\Roles;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirect authenticated users away from login page to their dashboard.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only redirect if user is on root path or login path
        if (Auth::check() && ($request->is('/') || $request->is('login'))) {
            $user = Auth::user();
            $userRole = UserRole::where('user_id', $user->id)->first();
            
            if ($userRole) {
                $role = Roles::find($userRole->role_id);
                
                if ($role) {
                    $redirectUrl = match ($role->name) {
                        'admin', 'manager' => '/admin',
                        'anggota' => '/anggota',
                        'kasir', 'cashier', 'bendahara' => '/kasir',
                        'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                        default => '/auth/login',
                    };
                    
                    return redirect($redirectUrl);
                }
            }
            
            return redirect('/anggota');
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get user's primary role
            $primaryRole = $user->roles()->first();
            
            if ($primaryRole) {
                $roleName = $primaryRole->name;
                
                // Redirect based on role
                switch ($roleName) {
                    case 'admin':
                        if (!$request->is('admin*')) {
                            return redirect('/admin');
                        }
                        break;
                    case 'anggota':
                        if (!$request->is('anggota*')) {
                            return redirect('/anggota');
                        }
                        break;
                    case 'kasir':
                        if (!$request->is('kasir*')) {
                            return redirect('/kasir');
                        }
                        break;
                    case 'spv':
                        if (!$request->is('spv*')) {
                            return redirect('/spv');
                        }
                        break;
                    default:
                        // If no specific role, redirect to anggota by default
                        if (!$request->is('anggota*')) {
                            return redirect('/anggota');
                        }
                        break;
                }
            }
        }
        
        return $next($request);
    }
}

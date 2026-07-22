<?php

namespace App\Http\Responses;

use App\Models\Roles;
use App\Models\UserRole;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('CustomLoginResponse: No authenticated user found');

            return redirect()->to('/admin');
        }

        Log::info('CustomLoginResponse: Processing login for user', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $userRole = UserRole::where('user_id', $user->id)->first();

        if ($userRole) {
            $role = Roles::find($userRole->role_id);

            if ($role) {
                // Role aktif: admin, spv, kasir, anggota. Petugas offline (tanpa panel).
                $redirectUrl = match ($role->name) {
                    'admin', 'manager' => '/admin',
                    'kasir', 'cashier', 'bendahara' => '/kasir',
                    'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                    'anggota' => '/anggota',
                    default => '/auth/login',
                };

                Log::info('CustomLoginResponse: Redirecting user', [
                    'user_id' => $user->id,
                    'role' => $role->name,
                    'redirect_url' => $redirectUrl,
                ]);

                Session::forget('intended');
                Session::save();

                return redirect()->to($redirectUrl);
            }
        }

        Log::warning('CustomLoginResponse: No role found for user, using default redirect', [
            'user_id' => $user->id,
        ]);

        Session::forget('intended');
        Session::save();

        return redirect()->to('/admin');
    }
}

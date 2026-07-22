<?php

namespace App\Filament\Pages\Auth;

use App\Models\Roles;
use App\Models\UserRole;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Login extends BaseLogin
{
    /**
     * Authenticate with email/password only (no CAPTCHA).
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            Notification::make()
                ->title('Login Gagal')
                ->body('Email atau password yang Anda masukkan salah. Silakan periksa kembali dan coba lagi.')
                ->danger()
                ->duration(5000)
                ->send();

            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof \Filament\Models\Contracts\FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk mengakses panel ini.')
                ->danger()
                ->duration(5000)
                ->send();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * Get the redirect URL after successful authentication.
     */
    protected function getRedirectUrl(): string
    {
        $user = Auth::user();

        if (! $user) {
            Log::warning('Login: No authenticated user found');

            return '/auth/login';
        }

        Log::info('Login: Processing redirect for user', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $userRole = UserRole::where('user_id', $user->id)->first();

        if ($userRole) {
            $role = Roles::find($userRole->role_id);

            if ($role) {
                $redirectUrl = match ($role->name) {
                    'admin', 'manager' => '/admin',
                    'kasir', 'cashier', 'bendahara' => '/kasir',
                    'spv', 'kepalayayasan', 'kepala_yayasan' => '/spv',
                    'anggota' => '/anggota',
                    default => '/auth/login',
                };

                Log::info('Login: Redirecting user', [
                    'user_id' => $user->id,
                    'role' => $role->name,
                    'redirect_url' => $redirectUrl,
                ]);

                return $redirectUrl;
            }
        }

        Log::warning('Login: No role found for user, using default redirect', [
            'user_id' => $user->id,
        ]);

        return '/auth/login';
    }
}

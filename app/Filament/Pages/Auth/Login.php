<?php

namespace App\Filament\Pages\Auth;

use App\Models\Roles;
use App\Models\UserRole;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override form to include Google reCAPTCHA.
     */
    protected function getForms(): array
    {
        $schema = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        // Only add reCAPTCHA fields if it is not disabled in config
        if (!config('captcha.disabled', false)) {
            $schema[] = Forms\Components\Hidden::make('g_recaptcha')
                ->dehydrated();
            $schema[] = Forms\Components\View::make('components.recaptcha-field')
                ->columnSpanFull();
        }

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($schema)
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Authenticate with reCAPTCHA validation.
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

        // Validate reCAPTCHA only if it is not disabled
        if (!config('captcha.disabled', false)) {
            try {
                Validator::make([
                    'g_recaptcha' => $data['g_recaptcha'] ?? null,
                ], [
                    'g_recaptcha' => ['required', 'captcha'],
                ], [
                    'g_recaptcha.required' => 'Silakan centang reCAPTCHA.',
                    'g_recaptcha.captcha' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
                ])->validate();
            } catch (ValidationException $e) {
                // Reset reCAPTCHA widget
                $this->dispatch('reset-recaptcha');
                
                Notification::make()
                    ->title('Verifikasi reCAPTCHA Gagal')
                    ->body('Silakan centang kotak "I\'m not a robot" dan coba lagi.')
                    ->danger()
                    ->send();
                throw $e;
            }
        }

        // Attempt authentication
        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            // Reset reCAPTCHA after failed login
            $this->dispatch('reset-recaptcha');
            
            Notification::make()
                ->title('Login Gagal')
                ->body('Email atau password yang Anda masukkan salah. Silakan periksa kembali dan coba lagi.')
                ->danger()
                ->duration(5000)
                ->send();
            
            $this->throwFailureValidationException();
        }

        Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false);

        $user = Filament::auth()->user();

        // Check panel access
        if (
            ($user instanceof \Filament\Models\Contracts\FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            
            // Reset reCAPTCHA after access denied
            $this->dispatch('reset-recaptcha');
            
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
     * This method is called by Filament after login.
     */
    protected function getRedirectUrl(): string
    {
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('Login: No authenticated user found');
            return '/auth/login';
        }

        Log::info('Login: Processing redirect for user', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Get user's role using UserRole model
        $userRole = UserRole::where('user_id', $user->id)->first();

        if ($userRole) {
            $role = Roles::find($userRole->role_id);

            if ($role) {
                // Petugas offline — tidak diarahkan ke panel manapun
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

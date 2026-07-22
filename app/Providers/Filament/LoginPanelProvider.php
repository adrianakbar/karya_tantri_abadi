<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LoginPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('login')
            ->path('/auth')
            ->login(Login::class)
            ->loginRouteSlug('login')
            ->colors([
                'primary' => Color::Green,
            ])
            ->favicon(asset('img/logo-karya-tantri-abadi.png'))
            ->brandName('Karya Tantri Abadi')
            ->brandLogo(fn() => view('components.custom-brand', [
                'logo' => asset('img/logo-karya-tantri-abadi.png'),
                'title' => 'Karya Tantri Abadi',
            ]))
            ->renderHook('panels::head.end', fn () => view('filament.head'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RedirectIfAuthenticated::class, // Redirect authenticated users to their dashboard
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

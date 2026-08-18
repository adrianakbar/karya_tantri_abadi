<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Petugas\NasabahResource;
use App\Http\Middleware\Authenticate;
use Filament\Pages;
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

class PetugasPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('petugas')
            ->path('/petugas')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->favicon(asset('img/logo-karya-tantri-abadi.png'))
            ->brandName('Karya Tantri Abadi - Petugas')
            ->resources([
                NasabahResource::class,
            ])
            ->pages([
            ])
            ->brandLogo(fn () => view('components.custom-brand', [
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

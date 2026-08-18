<?php

namespace App\Providers\Filament;

use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KepalayayasanPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('spv')
            ->path('/spv')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->favicon(asset('img/logo-karya-tantri-abadi.png'))
            ->brandName('Karya Tantri Abadi - SPV')
            ->resources([
                // SPV: setujui/tolak pinjaman + monitoring laporan
                \App\Filament\Resources\LoanResource::class,
            ])
            ->pages([
                \App\Filament\Pages\LoanReport::class,
                \App\Filament\Pages\FinancialReport::class,
                // SHU dihapus
            ])
            ->brandLogo(fn () => view('components.custom-brand', [
                'logo' => asset('img/logo-karya-tantri-abadi.png'),
                'title' => 'Karya Tantri Abadi',
            ]))
            ->renderHook('panels::head.end', fn () => view('filament.head'))
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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

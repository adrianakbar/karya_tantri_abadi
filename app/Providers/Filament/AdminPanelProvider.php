<?php

namespace App\Providers\Filament;

use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->favicon(asset('img/logo-karya-tantri-abadi.png'))
            ->brandName('Karya Tantri Abadi - Admin')
            ->resources([
                \App\Filament\Resources\UserResource::class,
                // Simpanan/tabungan: admin pantau, kasir catat
                \App\Filament\Resources\SavingResource::class,
                \App\Filament\Resources\SavingsTypeResource::class,
                \App\Filament\Resources\SystemSettingResource::class,
                // Hak akses (Role/Permission/UserRole) dihapus dari UI
                \App\Filament\Resources\LoanResource::class,
                // LoanTypeResource dihapus — jenis pinjaman tunggal: Kelompok
                \App\Filament\Resources\ExpenseResource::class,
                \App\Filament\Resources\ExpenseCategoryResource::class,
                \App\Filament\Resources\ActivityLogResource::class,
                \App\Filament\Resources\AuthLogResource::class,
                \App\Filament\Resources\DataChangeLogResource::class,
            ])
            // Jangan auto-discover pages (supaya AuditTrail/SHU tidak muncul)
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\FinancialReport::class,
                \App\Filament\Pages\LoanReport::class,
                \App\Filament\Pages\SavingsReport::class,
                \App\Filament\Pages\ExpenseReport::class,
                \App\Filament\Pages\BackupManagement::class,
            ])
            ->brandLogo(fn () => view('components.custom-brand', [
                'logo' => asset('img/logo-karya-tantri-abadi.png'),
                'title' => 'Karya Tantri Abadi',
            ]))
            ->renderHook('panels::head.end', fn () => view('filament.head-with-tour'))
            ->widgets([
                \App\Filament\Widgets\RekapAnggota::class,
                \App\Filament\Widgets\RekapPinjaman::class,
                \App\Filament\Widgets\RekapSimpanan::class,
                \App\Filament\Widgets\RekapPengeluaran::class,
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

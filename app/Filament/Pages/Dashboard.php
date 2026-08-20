<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Pages\Concerns\HasWidgets;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RekapKeuanganStats::class,
            \App\Filament\Widgets\RekapPinjamanStats::class,
            // Inventory/POS dinonaktifkan untuk Karya Tantri Abadi
            // \App\Filament\Widgets\InventoryOverview::class,
            // \App\Filament\Widgets\RekapPenjualan::class,
            \App\Filament\Widgets\RekapAnggota::class,
            \App\Filament\Widgets\RekapAnggotaBaru::class,
            \App\Filament\Widgets\RekapPinjaman::class,
            \App\Filament\Widgets\RekapSimpanan::class,
            \App\Filament\Widgets\RecentLoanRequests::class,
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RekapPinjamanStats extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        $base = fn () => Loan::where('cooperation_id', $cooperationId);

        $outstanding = (clone $base())
            ->whereIn('status', ['approved', 'disbursed', 'active', 'overdue'])
            ->sum('remaining_balance');

        $activeCount = (clone $base())
            ->whereIn('status', ['approved', 'disbursed', 'active', 'overdue'])
            ->count();

        $pending = (clone $base())->where('status', 'pending')->count();
        $completed = (clone $base())->where('status', 'completed')->count();
        $rejected = (clone $base())->where('status', 'rejected')->count();
        $overdue = (clone $base())->where('status', 'overdue')->count();

        return [
            Stat::make('Total Pinjaman Beredar', 'Rp ' . number_format($outstanding, 0, ',', '.'))
                ->description($activeCount . ' pinjaman aktif')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Menunggu Persetujuan', $pending)
                ->description('Perlu ditinjau')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),

            Stat::make('Jatuh Tempo / Terlambat', $overdue)
                ->description('Butuh penagihan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'gray'),

            Stat::make('Lunas', $completed)
                ->description($rejected . ' ditolak')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}

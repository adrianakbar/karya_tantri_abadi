<?php

namespace App\Filament\Widgets;

use App\Models\CashFlow;
use App\Models\SavingsTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RekapKeuanganStats extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $cooperationId = auth()->user()->cooperation_id;

        $inflow = CashFlow::where('cooperation_id', $cooperationId)
            ->where('type', 'inflow')
            ->sum('amount');

        $outflow = CashFlow::where('cooperation_id', $cooperationId)
            ->where('type', 'outflow')
            ->sum('amount');

        $balance = $inflow - $outflow;

        $totalSavings = SavingsTransaction::where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->sum('amount');

        $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        return [
            Stat::make('Total Kas Masuk', $rp($inflow))
                ->description('Seluruh pemasukan')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Total Kas Keluar', $rp($outflow))
                ->description('Seluruh pengeluaran')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),

            Stat::make('Saldo Kas Bersih', $rp($balance))
                ->description($balance >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-scale' : 'heroicon-m-exclamation-triangle')
                ->color($balance >= 0 ? 'primary' : 'danger'),

            Stat::make('Total Simpanan Terkumpul', $rp($totalSavings))
                ->description('Transaksi selesai')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}

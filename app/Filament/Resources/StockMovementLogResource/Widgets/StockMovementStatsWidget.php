<?php

namespace App\Filament\Resources\StockMovementLogResource\Widgets;

use App\Models\StockMovementLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockMovementStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user || !$user->cooperation_id) {
            return [];
        }

        $cooperationId = $user->cooperation_id;

        try {
            // Today's movements
            $todayMovements = StockMovementLog::where('cooperation_id', $cooperationId)
                ->whereDate('created_at', today())
                ->count();

            // Today's stock in
            $todayStockIn = StockMovementLog::where('cooperation_id', $cooperationId)
                ->whereDate('created_at', today())
                ->whereIn('type', ['in', 'purchase', 'return'])
                ->sum('quantity');

            // Today's stock out
            $todayStockOut = StockMovementLog::where('cooperation_id', $cooperationId)
                ->whereDate('created_at', today())
                ->whereIn('type', ['out', 'sale', 'damaged'])
                ->sum('quantity');

            // Products that became low stock today
            $lowStockToday = StockMovementLog::where('cooperation_id', $cooperationId)
                ->whereDate('created_at', today())
                ->whereHas('product', function ($query) {
                    $query->whereColumn('stock_movement_logs.stock_after', '<', 'products.min_stock');
                })
                ->distinct('product_id')
                ->count();

            return [
                Stat::make('Perubahan Hari Ini', number_format($todayMovements))
                    ->description('Total transaksi stok')
                    ->descriptionIcon('heroicon-m-clipboard-document-list')
                    ->color('primary'),

                Stat::make('Stok Masuk', number_format($todayStockIn))
                    ->description('Unit masuk hari ini')
                    ->descriptionIcon('heroicon-m-arrow-down-tray')
                    ->color('success'),

                Stat::make('Stok Keluar', number_format($todayStockOut))
                    ->description('Unit keluar hari ini')
                    ->descriptionIcon('heroicon-m-arrow-up-tray')
                    ->color('danger'),

                Stat::make('Produk Stok Rendah', number_format($lowStockToday))
                    ->description('Produk jadi stok rendah hari ini')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Error', 0)
                    ->description('Gagal memuat statistik')
                    ->color('danger'),
            ];
        }
    }
}

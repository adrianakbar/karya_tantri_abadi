<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user || !$user->cooperation_id) {
            return [
                Stat::make('Error', 0)
                    ->description('User tidak memiliki cooperation')
                    ->color('danger'),
            ];
        }

        $cooperationId = $user->cooperation_id;

        $totalProducts = Product::where('cooperation_id', $cooperationId)->count();
        
        $outOfStockProducts = Product::where('cooperation_id', $cooperationId)
            ->where('current_stock', '<=', 0)
            ->count();
        
        $lowStockProducts = Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->where('current_stock', '>', 0)
            ->count();

        $normalStockProducts = Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '>=', 'min_stock')
            ->count();

        return [
            Stat::make('Total Produk', $totalProducts)
                ->description('Jumlah semua produk')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Stok Normal', $normalStockProducts)
                ->description('Produk dengan stok normal')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Stok Rendah', $lowStockProducts)
                ->description('Produk dengan stok di bawah minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Stok Habis', $outOfStockProducts)
                ->description('Produk yang stoknya habis')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}

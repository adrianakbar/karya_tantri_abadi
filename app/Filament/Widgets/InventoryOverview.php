<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use Carbon\Carbon;

class InventoryOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return false;
    }


    protected function getStats(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        
        // Product statistics
        $totalProducts = Product::where('cooperation_id', $cooperationId)->count();
        $lowStockProducts = Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)
            ->count();
        $outOfStockProducts = Product::where('cooperation_id', $cooperationId)
            ->where('current_stock', '<=', 0)
            ->count();
        
        // Sales statistics (current month)
        $currentMonth = Carbon::now()->format('Y-m');
        $monthlySales = Sale::where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->sum('total_amount');
        
        // Purchase statistics (current month)
        $monthlyPurchases = Purchase::where('cooperation_id', $cooperationId)
            ->where('status', 'received')
            ->whereYear('purchase_date', Carbon::now()->year)
            ->whereMonth('purchase_date', Carbon::now()->month)
            ->sum('grand_total');
        
        // Total inventory value
        $inventoryValue = Product::where('cooperation_id', $cooperationId)
            ->selectRaw('SUM(current_stock * purchase_price) as total_value')
            ->first()
            ->total_value ?? 0;

        return [
            Stat::make('Total Produk', $totalProducts)
                ->description('Produk yang terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Stok Rendah', $lowStockProducts)
                ->description('Produk dengan stok rendah')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),

            Stat::make('Stok Habis', $outOfStockProducts)
                ->description('Produk yang habis')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockProducts > 0 ? 'danger' : 'success'),

            Stat::make('Nilai Inventaris', 'Rp ' . number_format($inventoryValue, 0, ',', '.'))
                ->description('Total nilai stok barang')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('Penjualan Bulan Ini', 'Rp ' . number_format($monthlySales, 0, ',', '.'))
                ->description('Total penjualan ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($monthlyPurchases, 0, ',', '.'))
                ->description('Total pembelian ' . Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
        ];
    }
}

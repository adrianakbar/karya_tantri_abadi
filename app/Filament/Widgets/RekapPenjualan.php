<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapPenjualan extends ChartWidget
{
    protected static ?string $heading = 'Statistik Penjualan (6 Bulan Terakhir)';

    public static function canView(): bool
    {
        return false;
    }


    protected function getData(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        
        // Get sales data for last 6 months
        $salesData = Sale::where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->where('sale_date', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Generate labels for last 6 months
        $months = [];
        $totals = [];
        $counts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->translatedFormat('M Y');
            
            $months[] = $monthLabel;
            
            $saleData = $salesData->firstWhere('month', $monthKey);
            $totals[] = $saleData ? $saleData->total : 0;
            $counts[] = $saleData ? $saleData->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $totals,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)', // blue
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Rp " + context.parsed.y.toLocaleString("id-ID");
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + value.toLocaleString("id-ID");
                        }',
                    ],
                ],
            ],
        ];
    }
}

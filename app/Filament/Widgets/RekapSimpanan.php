<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapSimpanan extends ChartWidget
{
    protected static ?string $heading = 'Statistik Tabungan (6 Bulan Terakhir)';

    protected function getData(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        
        // Get savings data for last 6 months
        $savingsData = SavingsTransaction::where('savings_transactions.cooperation_id', $cooperationId)
            ->where('savings_transactions.status', 'completed')
            ->where('transaction_date', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total'),
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
            
            $savingData = $savingsData->firstWhere('month', $monthKey);
            
            $totals[] = $savingData ? $savingData->total : 0;
            $counts[] = $savingData ? $savingData->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Tabungan (Rp)',
                    'data' => $totals,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)', // green
                    'borderColor' => 'rgba(34, 197, 94, 1)',
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
                            return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString("id-ID");
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

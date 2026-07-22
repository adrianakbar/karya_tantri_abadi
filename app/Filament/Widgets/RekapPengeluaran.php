<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapPengeluaran extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pengeluaran Berdasarkan Kategori';

    protected function getData(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        
        // Get expense data by category for current month
        $expenseData = Expense::where('expenses.cooperation_id', $cooperationId)
            ->whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select(
                'expense_categories.name as category_name',
                DB::raw('SUM(expenses.amount) as total')
            )
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderBy('total', 'desc')
            ->get();

        $categories = $expenseData->pluck('category_name')->toArray();
        $totals = $expenseData->pluck('total')->toArray();
        
        // Generate colors for each category
        $colors = [
            'rgba(239, 68, 68, 0.7)',   // red
            'rgba(251, 146, 60, 0.7)',  // orange
            'rgba(245, 158, 11, 0.7)',  // amber
            'rgba(132, 204, 22, 0.7)',  // lime
            'rgba(34, 197, 94, 0.7)',   // green
            'rgba(20, 184, 166, 0.7)',  // teal
            'rgba(59, 130, 246, 0.7)',  // blue
            'rgba(147, 51, 234, 0.7)',  // purple
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran (Rp)',
                    'data' => $totals,
                    'backgroundColor' => array_slice($colors, 0, count($totals)),
                    'borderColor' => array_map(fn($c) => str_replace('0.7', '1', $c), array_slice($colors, 0, count($totals))),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $categories,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": Rp " + context.parsed.toLocaleString("id-ID");
                        }',
                    ],
                ],
            ],
        ];
    }
}

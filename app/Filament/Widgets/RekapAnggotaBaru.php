<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RekapAnggotaBaru extends ChartWidget
{
    protected static ?string $heading = 'Anggota Baru (6 Bulan Terakhir)';

    protected function getData(): array
    {
        $cooperationId = auth()->user()->cooperation_id;

        $rows = User::where('cooperation_id', $cooperationId)
            ->whereNotNull('join_date')
            ->where('join_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(join_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');
            $row = $rows->firstWhere('month', $key);
            $data[] = $row ? $row->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Anggota Baru',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
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
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0, 'stepSize' => 1],
                ],
            ],
        ];
    }
}

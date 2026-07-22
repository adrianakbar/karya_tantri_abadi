<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Loan;
use Carbon\Carbon;

class RekapPinjaman extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pinjaman Berdasarkan Status';

    protected function getData(): array
    {
        $cooperationId = auth()->user()->cooperation_id;
        
        // Count loans by status
        $pending = Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'pending')
            ->count();
            
        $active = Loan::where('cooperation_id', $cooperationId)
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->count();
            
        $completed = Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->count();
            
        $rejected = Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'rejected')
            ->count();
            
        $overdue = Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'overdue')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pinjaman',
                    'data' => [$pending, $active, $completed, $rejected, $overdue],
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.7)',  // amber - pending
                        'rgba(59, 130, 246, 0.7)',  // blue - active
                        'rgba(34, 197, 94, 0.7)',   // green - completed
                        'rgba(239, 68, 68, 0.7)',   // red - rejected
                        'rgba(234, 88, 12, 0.7)',   // orange - overdue
                    ],
                    'borderColor' => [
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(234, 88, 12, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pending', 'Aktif', 'Lunas', 'Ditolak', 'Terlambat'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}

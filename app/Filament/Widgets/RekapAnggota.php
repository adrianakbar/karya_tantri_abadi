<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class RekapAnggota extends ChartWidget
{
    protected static ?string $heading = 'Statistik Anggota';

    protected function getData(): array
    {
        $maleActive = User::where('gender', 'male')->where('is_active', true)->count();
        $maleInactive = User::where('gender', 'male')->where('is_active', false)->count();
        $femaleActive = User::where('gender', 'female')->where('is_active', true)->count();
        $femaleInactive = User::where('gender', 'female')->where('is_active', false)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Aktif',
                    'data' => [$maleActive, $femaleActive],
                    'backgroundColor' => 'rgba(52, 211, 153, 0.7)', // hijau transparan
                    'borderColor' => 'rgba(52, 211, 153, 1)', // hijau solid
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => 'rgba(52, 211, 153, 1)',
                ],
                [
                    'label' => 'Tidak Aktif',
                    'data' => [$maleInactive, $femaleInactive],
                    'backgroundColor' => 'rgba(248, 113, 113, 0.7)', // merah transparan
                    'borderColor' => 'rgba(248, 113, 113, 1)', // merah solid
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => 'rgba(248, 113, 113, 1)',
                ],
            ],
            'labels' => ['Laki-laki', 'Perempuan'],
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
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Distribusi Anggota berdasarkan Gender dan Status',
                    'font' => [
                        'size' => 12,
                        'weight' => 'bold',
                    ],
                    'padding' => [
                        'top' => 10,
                        'bottom' => 20,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => '#111827', // abu gelap
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold',
                    ],
                    'bodyFont' => [
                        'size' => 13,
                    ],
                ],
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'font' => [
                            'size' => 13,
                        ],
                        'color' => '#374151', // text-gray-700
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                        ],
                        'color' => '#374151',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                        ],
                        'color' => '#374151',
                    ],
                ],
            ],
        ];
    }
    
}

<?php

namespace App\Exports;

use App\Models\ShuDistribution;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ShuCalculationExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return ShuDistribution::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['calculator', 'distributor'])
            ->get()
            ->map(function ($shu) {
                return [
                    'Tahun' => $shu->year,
                    'Total Pendapatan' => 'Rp ' . number_format($shu->total_revenue ?? 0, 0, ',', '.'),
                    'Total Pengeluaran' => 'Rp ' . number_format($shu->total_expenses ?? 0, 0, ',', '.'),
                    'Total SHU' => 'Rp ' . number_format($shu->total_shu, 0, ',', '.'),
                    'Tanggal Distribusi' => $shu->distribution_date ? $shu->distribution_date->format('d/m/Y') : '-',
                    'Status' => match($shu->status) {
                        'calculated' => 'Dihitung',
                        'distributed' => 'Didistribusi',
                        'pending' => 'Pending',
                        default => $shu->status,
                    },
                    'Dihitung Oleh' => $shu->calculator->name ?? '-',
                    'Didistribusi Oleh' => $shu->distributor->name ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Total Pendapatan',
            'Total Pengeluaran',
            'Total SHU',
            'Tanggal Distribusi',
            'Status',
            'Dihitung Oleh',
            'Didistribusi Oleh',
        ];
    }

    public function title(): string
    {
        return 'Perhitungan SHU';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Laporan Perhitungan SHU - Karya Tantri Abadi');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Header style
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF70AD47'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Data rows style
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 3) {
            $sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($row = 4; $row <= $lastRow; $row++) {
                if (($row - 4) % 2 == 0) {
                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}


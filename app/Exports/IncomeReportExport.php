<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IncomeReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return Sale::where('cooperation_id', Auth::user()->cooperation_id)
            ->where('status', 'completed')
            ->with(['customer'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'Tanggal Penjualan' => $sale->sale_date->format('d/m/Y'),
                    'No. Penjualan' => $sale->sale_number,
                    'Customer' => $sale->customer->name ?? 'Unknown',
                    'Subtotal' => 'Rp ' . number_format($sale->subtotal, 0, ',', '.'),
                    'Total Penjualan' => 'Rp ' . number_format($sale->total_amount, 0, ',', '.'),
                    'Status' => 'Selesai',
                    'Metode Pembayaran' => $sale->payment_method ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal Penjualan',
            'No. Penjualan',
            'Customer',
            'Subtotal',
            'Total Penjualan',
            'Status',
            'Metode Pembayaran',
        ];
    }

    public function title(): string
    {
        return 'Laporan Pemasukan';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Laporan Pemasukan - Karya Tantri Abadi');
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
        $sheet->getStyle('A3:G3')->applyFromArray([
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
            $sheet->getStyle("A4:G{$lastRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}

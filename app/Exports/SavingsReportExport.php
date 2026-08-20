<?php

namespace App\Exports;

use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SavingsReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return SavingsTransaction::where('cooperation_id', Auth::user()->cooperation_id)
            ->where('status', 'completed')
            ->with(['user', 'savingsType', 'processor'])
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'Tanggal' => $transaction->transaction_date->format('d/m/Y'),
                    'No. Transaksi' => $transaction->transaction_number,
                    'Anggota' => $transaction->user->name ?? 'Unknown',
                    'No. Anggota' => $transaction->user->member_number ?? '-',
                    'Jenis Simpanan' => $transaction->savingsType->name ?? 'Unknown',
                    'Jumlah' => 'Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'Status' => 'Completed',
                    'Diproses Oleh' => $transaction->processor->name ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Transaksi',
            'Anggota',
            'No. Anggota',
            'Jenis Simpanan',
            'Jumlah',
            'Status',
            'Diproses Oleh',
        ];
    }

    public function title(): string
    {
        return 'Laporan Simpanan';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Laporan Simpanan - Karya Tantri Abadi');
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

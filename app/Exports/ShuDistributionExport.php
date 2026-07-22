<?php

namespace App\Exports;

use App\Models\ShuMemberShare;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ShuDistributionExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return ShuMemberShare::whereHas('distribution', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            })
            ->with(['distribution', 'user'])
            ->orderBy('shu_amount', 'desc')
            ->get()
            ->map(function ($share) {
                return [
                    'Tahun' => $share->distribution->year,
                    'Nama Anggota' => $share->user->name ?? 'Unknown',
                    'No. Anggota' => $share->user->member_number ?? '-',
                    'Bagian Simpanan' => 'Rp ' . number_format($share->savings_contribution, 0, ',', '.'),
                    'Bagian Transaksi' => 'Rp ' . number_format($share->transaction_contribution, 0, ',', '.'),
                    'Total Bagian' => 'Rp ' . number_format($share->shu_amount, 0, ',', '.'),
                    'Status' => match($share->status) {
                        'paid' => 'Dibayar',
                        'pending' => 'Pending',
                        default => $share->status,
                    },
                    'Tanggal Bayar' => $share->paid_at ? $share->paid_at->format('d/m/Y H:i') : '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Nama Anggota',
            'No. Anggota',
            'Bagian Simpanan',
            'Bagian Transaksi',
            'Total Bagian',
            'Status',
            'Tanggal Bayar',
        ];
    }

    public function title(): string
    {
        return 'Distribusi SHU Anggota';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Laporan Distribusi SHU Anggota - Karya Tantri Abadi');
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


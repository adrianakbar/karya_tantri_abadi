<?php

namespace App\Exports;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return Loan::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['user', 'loanType'])
            ->get()
            ->map(function ($loan) {
                return [
                    'No. Pinjaman' => $loan->loan_number,
                    'Nama Anggota' => $loan->user->name ?? 'Unknown',
                    'No. Anggota' => $loan->user->member_number ?? '-',
                    'Jenis Pinjaman' => $loan->loanType->name ?? 'Unknown',
                    'Jumlah Pinjaman' => 'Rp ' . number_format($loan->loan_amount, 0, ',', '.'),
                    'Bunga (%)' => $loan->interest_rate . '%',
                    'Tenor' => $loan->tenor . ' bulan',
                    'Tanggal Pencairan' => $loan->disbursement_date ? $loan->disbursement_date->format('d/m/Y') : '-',
                    'Jatuh Tempo' => $loan->due_date ? $loan->due_date->format('d/m/Y') : '-',
                    'Status' => match($loan->status) {
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'defaulted' => 'Macet',
                        'pending' => 'Pending',
                        default => $loan->status,
                    },
                    // 'Diproses Oleh' => $loan->processor->name ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No. Pinjaman',
            'Nama Anggota',
            'No. Anggota',
            'Jenis Pinjaman',
            'Jumlah Pinjaman',
            'Bunga (%)',
            'Tenor',
            'Tanggal Pencairan',
            'Jatuh Tempo',
            'Status',
            'Diproses Oleh',
        ];
    }

    public function title(): string
    {
        return 'Laporan Pinjaman';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'Laporan Pinjaman - Karya Tantri Abadi');
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
        $sheet->getStyle('A3:K3')->applyFromArray([
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
            $sheet->getStyle("A4:K{$lastRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}


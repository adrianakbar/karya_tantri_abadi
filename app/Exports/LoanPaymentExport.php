<?php

namespace App\Exports;

use App\Models\LoanPayment;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanPaymentExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function collection()
    {
        return LoanPayment::whereHas('loan', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            })
            ->with(['loan.user', 'loan.loanType'])
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'No. Pembayaran' => $payment->payment_number,
                    'No. Pinjaman' => $payment->loan->loan_number,
                    'Nama Anggota' => $payment->loan->user->name ?? 'Unknown',
                    'Jenis Pinjaman' => $payment->loan->loanType->name ?? 'Unknown',
                    'Angsuran Ke' => $payment->installment_number,
                    'Tanggal Jatuh Tempo' => $payment->due_date ? $payment->due_date->format('d/m/Y') : '-',
                    'Tanggal Bayar' => $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-',
                    'Pokok' => 'Rp ' . number_format($payment->principal_amount, 0, ',', '.'),
                    'Bunga' => 'Rp ' . number_format($payment->interest_amount, 0, ',', '.'),
                    'Denda' => 'Rp ' . number_format($payment->penalty_amount, 0, ',', '.'),
                    'Total Bayar' => 'Rp ' . number_format($payment->total_amount, 0, ',', '.'),
                    'Status' => match($payment->status) {
                        'paid' => 'Lunas',
                        'pending' => 'Pending',
                        'overdue' => 'Terlambat',
                        default => $payment->status,
                    },
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No. Pembayaran',
            'No. Pinjaman',
            'Nama Anggota',
            'Jenis Pinjaman',
            'Angsuran Ke',
            'Tanggal Jatuh Tempo',
            'Tanggal Bayar',
            'Pokok',
            'Bunga',
            'Denda',
            'Total Bayar',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Laporan Pembayaran Pinjaman';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Laporan Pembayaran Pinjaman - Karya Tantri Abadi');
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
        $sheet->getStyle('A3:L3')->applyFromArray([
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
            $sheet->getStyle("A4:L{$lastRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}


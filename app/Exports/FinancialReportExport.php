<?php

namespace App\Exports;

use App\Models\CashFlow;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $data;
    protected $filterPeriod;
    protected $filterMonth;
    protected $filterYear;

    public function __construct($data, $filterPeriod, $filterMonth, $filterYear)
    {
        $this->data = $data;
        $this->filterPeriod = $filterPeriod;
        $this->filterMonth = $filterMonth;
        $this->filterYear = $filterYear;
    }

    public function collection()
    {
        // The data is already prepared and filtered by the FinancialReport page, so just map it.
        return $this->data->map(function ($transaction) {
            return [
                'Tanggal' => $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') : '-',
                'Keterangan' => $transaction->description ?? '-',
                'Jenis' => match($transaction->type) {
                    'inflow' => 'Pemasukan',
                    'outflow' => 'Pengeluaran',
                    'transfer' => 'Transfer',
                    default => $transaction->type,
                },
                'Kategori' => $transaction->category ?? '-',
                'Jumlah' => 'Rp ' . number_format($transaction->amount ?? 0, 0, ',', '.'),
                'Saldo Akhir' => 'Rp ' . number_format($transaction->balance_after ?? 0, 0, ',', '.'),
                'Referensi' => match($transaction->reference_type) {
                    'Sale' => 'Penjualan',
                    'Purchase' => 'Pembelian',
                    'Expense' => 'Pengeluaran',
                    'SavingsTransaction' => 'Simpanan',
                    'LoanPayment' => 'Pembayaran Pinjaman',
                    'Loan' => 'Pencairan Pinjaman',
                    default => $transaction->reference_type ?: '-',
                },
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Keterangan',
            'Jenis',
            'Kategori',
            'Jumlah',
            'Saldo Akhir',
            'Referensi',
        ];
    }

    public function title(): string
    {
        return 'Laporan Arus Kas';
    }

    public function styles(Worksheet $sheet)
    {
        // Title style
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Laporan Arus Kas - Karya Tantri Abadi');
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

<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Purchase;
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

class InventoryReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $type;

    public function __construct($type = 'stock')
    {
        $this->type = $type;
    }

    public function collection()
    {
        switch ($this->type) {
            case 'stock':
                return $this->getStockData();
            case 'purchases':
                return $this->getPurchasesData();
            case 'sales':
                return $this->getSalesData();
            case 'profit_loss':
                return $this->getProfitLossData();
            default:
                return $this->getStockData();
        }
    }

    private function getStockData()
    {
        return Product::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['category'])
            ->get()
            ->map(function ($product) {
                return [
                    'Kode Produk' => $product->code,
                    'Nama Produk' => $product->name,
                    'Kategori' => $product->category->name ?? 'Unknown',
                    'Stok' => $product->stock_quantity,
                    'Harga Beli' => 'Rp ' . number_format($product->purchase_price ?? 0, 0, ',', '.'),
                    'Harga Jual' => 'Rp ' . number_format($product->selling_price ?? 0, 0, ',', '.'),
                    'Status' => $product->stock_quantity > 0 ? 'Tersedia' : 'Habis',
                ];
            });
    }

    private function getPurchasesData()
    {
        return Purchase::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['supplier', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'Tanggal' => $purchase->purchase_date->format('d/m/Y'),
                    'No. Pembelian' => $purchase->purchase_number,
                    'Supplier' => $purchase->supplier->name ?? 'Unknown',
                    'Total Item' => $purchase->details->sum('quantity'),
                    'Total Pembelian' => 'Rp ' . number_format($purchase->total_amount, 0, ',', '.'),
                    'Status' => match($purchase->status) {
                        'pending' => 'Pending',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                        default => $purchase->status,
                    },
                ];
            });
    }

    private function getSalesData()
    {
        return Sale::where('cooperation_id', Auth::user()->cooperation_id)
            ->where('status', 'completed')
            ->with(['customer', 'details.product'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'Tanggal' => $sale->sale_date->format('d/m/Y'),
                    'No. Penjualan' => $sale->sale_number,
                    'Customer' => $sale->customer->name ?? 'Unknown',
                    'Total Item' => $sale->details->sum('quantity'),
                    'Total Penjualan' => 'Rp ' . number_format($sale->total_amount, 0, ',', '.'),
                    'Keuntungan' => 'Rp ' . number_format($this->calculateProfit($sale), 0, ',', '.'),
                ];
            });
    }

    private function getProfitLossData()
    {
        return Product::where('cooperation_id', Auth::user()->cooperation_id)
            ->get()
            ->map(function ($product) {
                $purchaseCost = ($product->purchase_price ?? 0) * $product->stock_quantity;
                $potentialSales = ($product->selling_price ?? 0) * $product->stock_quantity;
                $profit = $potentialSales - $purchaseCost;

                return [
                    'Kode Produk' => $product->code,
                    'Nama Produk' => $product->name,
                    'Stok' => $product->stock_quantity,
                    'Modal' => 'Rp ' . number_format($purchaseCost, 0, ',', '.'),
                    'Potensi Penjualan' => 'Rp ' . number_format($potentialSales, 0, ',', '.'),
                    'Keuntungan' => 'Rp ' . number_format($profit, 0, ',', '.'),
                ];
            });
    }

    private function calculateProfit($sale)
    {
        $profit = 0;
        foreach ($sale->details as $detail) {
            $cost = ($detail->product->purchase_price ?? 0) * $detail->quantity;
            $revenue = $detail->total_price;
            $profit += $revenue - $cost;
        }
        return $profit;
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'stock':
                return ['Kode Produk', 'Nama Produk', 'Kategori', 'Stok', 'Harga Beli', 'Harga Jual', 'Status'];
            case 'purchases':
                return ['Tanggal', 'No. Pembelian', 'Supplier', 'Total Item', 'Total Pembelian', 'Status'];
            case 'sales':
                return ['Tanggal', 'No. Penjualan', 'Customer', 'Total Item', 'Total Penjualan', 'Keuntungan'];
            case 'profit_loss':
                return ['Kode Produk', 'Nama Produk', 'Stok', 'Modal', 'Potensi Penjualan', 'Keuntungan'];
            default:
                return ['Kode Produk', 'Nama Produk', 'Kategori', 'Stok', 'Harga Beli', 'Harga Jual', 'Status'];
        }
    }

    public function title(): string
    {
        switch ($this->type) {
            case 'stock':
                return 'Laporan Stok Produk';
            case 'purchases':
                return 'Laporan Pembelian';
            case 'sales':
                return 'Laporan Penjualan';
            case 'profit_loss':
                return 'Laporan Laba Rugi';
            default:
                return 'Laporan Inventori';
        }
    }

    public function styles(Worksheet $sheet)
    {
        $titleText = 'Laporan Inventori - Karya Tantri Abadi';
        $columnCount = count($this->headings());

        // Title style
        $sheet->mergeCells('A1:' . chr(64 + $columnCount) . '1');
        $sheet->setCellValue('A1', $titleText);
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
        $lastColumn = chr(64 + $columnCount);
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
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
            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF2F2F2'],
                        ],
                    ]);
                }
            }
        }

        // Auto-size columns
        for ($i = 0; $i < $columnCount; $i++) {
            $column = chr(65 + $i);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height for title
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\SaleDetail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ReportExportService
{
    public function exportInventoryReport($filters = [])
    {
        $query = Product::query()
            ->where('cooperation_id', auth()->user()->cooperation_id)
            ->with('category');

        // Apply filters
        if (isset($filters['category_id']) && $filters['category_id']) {
            $query->where('product_category_id', $filters['category_id']);
        }

        if (isset($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
                    break;
                case 'out':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'normal':
                    $query->whereColumn('current_stock', '>', 'min_stock');
                    break;
            }
        }

        $products = $query->get();

        $data = [
            ['Laporan Inventaris - ' . now()->format('d/m/Y H:i')],
            [''],
            ['Kode Produk', 'Nama Produk', 'Kategori', 'Satuan', 'Stok Tersedia', 'Stok Minimum', 'Harga Beli', 'Harga Jual', 'Nilai Stok', 'Status Stok']
        ];

        foreach ($products as $product) {
            $stockValue = $product->current_stock * $product->purchase_price;
            $stockStatus = $this->getStockStatus($product);

            $data[] = [
                $product->code,
                $product->name,
                $product->category?->name ?? '-',
                $product->unit,
                $product->current_stock,
                $product->min_stock,
                $product->purchase_price,
                $product->selling_price,
                $stockValue,
                $stockStatus
            ];
        }

        return $data;
    }

    public function exportPurchaseReport($filters = [])
    {
        $query = Purchase::query()
            ->where('cooperation_id', auth()->user()->cooperation_id)
            ->with(['details', 'supplier']);

        // Apply date filters
        if (isset($filters['from']) && $filters['from']) {
            $query->whereDate('purchase_date', '>=', $filters['from']);
        }
        if (isset($filters['until']) && $filters['until']) {
            $query->whereDate('purchase_date', '<=', $filters['until']);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->get();

        $data = [
            ['Laporan Pembelian - ' . now()->format('d/m/Y H:i')],
            [''],
            ['No. Pembelian', 'No. Invoice', 'Tanggal', 'Supplier', 'Jumlah Item', 'Subtotal', 'Pajak', 'Diskon', 'Total', 'Status']
        ];

        foreach ($purchases as $purchase) {
            $itemCount = $purchase->details->sum('quantity');
            
            $data[] = [
                $purchase->purchase_number,
                $purchase->invoice_number ?? '-',
                $purchase->purchase_date->format('d/m/Y'),
                $purchase->supplier?->name ?? '-',
                $itemCount,
                $purchase->total_amount,
                $purchase->tax_amount,
                $purchase->discount_amount,
                $purchase->grand_total,
                $this->getPurchaseStatus($purchase->status)
            ];
        }

        return $data;
    }

    private function getStockStatus($product)
    {
        if ($product->current_stock <= 0) {
            return 'Habis';
        } elseif ($product->current_stock <= $product->min_stock) {
            return 'Rendah';
        } else {
            return 'Normal';
        }
    }

    private function getSaleStatus($status)
    {
        return match($status) {
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    private function getPurchaseStatus($status)
    {
        return match($status) {
            'pending' => 'Pending',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }
}

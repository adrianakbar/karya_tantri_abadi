<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        Log::info("SaleObserver::created called for sale: " . $sale->id);
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        Log::info("SaleObserver::updated called", [
            'sale_id' => $sale->id,
            'status_changed' => $sale->wasChanged('status'),
            'old_status' => $sale->getOriginal('status'),
            'new_status' => $sale->status
        ]);

        if ($sale->wasChanged('status') && $sale->status === 'completed') {
            Log::info("Reducing product stock for sale: " . $sale->sale_number);
            $this->reduceProductStock($sale);
        }

        if ($sale->wasChanged('status') && $sale->getOriginal('status') === 'completed' && $sale->status !== 'completed') {
            Log::info("Restoring product stock for sale: " . $sale->sale_number);
            $this->restoreProductStock($sale);
        }
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        if ($sale->status === 'completed') {
            $this->restoreProductStock($sale);
        }
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        //
    }

    /**
     * Handle the Sale "force deleted" event.
     */
    public function forceDeleted(Sale $sale): void
    {
        //
    }

    /**
     * Kurangi stok produk berdasarkan detail penjualan
     */
    private function reduceProductStock(Sale $sale): void
    {
        foreach ($sale->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $oldStock = $product->current_stock;
                $newStock = max(0, $product->current_stock - $detail->quantity);

                Product::withoutEvents(function () use ($product, $newStock) {
                    $product->update(['current_stock' => $newStock]);
                });

                StockMovementLog::create([
                    'cooperation_id' => $sale->cooperation_id,
                    'product_id' => $product->id,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'type' => 'out',
                    'quantity' => $detail->quantity,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'notes' => "Penjualan produk - {$sale->sale_number}",
                    'created_by' => auth()->id() ?? null,
                ]);

                Log::info("Stock reduced for product {$product->name}: -{$detail->quantity} units from sale {$sale->sale_number}");

                if ($newStock <= $product->min_stock) {
                    Log::warning("Low stock alert for product {$product->name}: current stock {$newStock}, minimum stock {$product->min_stock}");
                }
            }
        }
    }

    /**
     * Kembalikan stok produk (tambah stok kembali)
     */
    private function restoreProductStock(Sale $sale): void
    {
        foreach ($sale->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $oldStock = $product->current_stock;

                Product::withoutEvents(function () use ($product, $detail) {
                    $product->increment('current_stock', $detail->quantity);
                });

                $newStock = $product->fresh()->current_stock;

                StockMovementLog::create([
                    'cooperation_id' => $sale->cooperation_id,
                    'product_id' => $product->id,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'type' => 'in',
                    'quantity' => $detail->quantity,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'notes' => "Pembatalan penjualan - {$sale->sale_number}",
                    'created_by' => auth()->id() ?? null,
                ]);

                Log::info("Stock restored for product {$product->name}: +{$detail->quantity} units from cancelled sale {$sale->sale_number}");
            }
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        Log::info("PurchaseObserver::created called", [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status
        ]);
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        // Debug log
        Log::info("PurchaseObserver::updated called", [
            'purchase_id' => $purchase->id,
            'status_changed' => $purchase->wasChanged('status'),
            'old_status' => $purchase->getOriginal('status'),
            'new_status' => $purchase->status
        ]);

        if ($purchase->wasChanged('status') && $purchase->status === 'received') {
            Log::info("Updating product stock for purchase: " . $purchase->purchase_number);
            $this->updateProductStock($purchase);
        }

        if ($purchase->wasChanged('status') && $purchase->getOriginal('status') === 'received' && $purchase->status !== 'received') {
            Log::info("Reverting product stock for purchase: " . $purchase->purchase_number);
            $this->revertProductStock($purchase);
        }
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        if ($purchase->status === 'received') {
            $this->revertProductStock($purchase);
        }
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        //
    }

    /**
     * Update stok produk berdasarkan detail pembelian
     */
    private function updateProductStock(Purchase $purchase): void
    {
        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $oldStock = $product->current_stock;

                Product::withoutEvents(function () use ($product, $detail) {
                    $product->increment('current_stock', $detail->quantity);
                });

                $newStock = $product->fresh()->current_stock;

                StockMovementLog::create([
                    'cooperation_id' => $purchase->cooperation_id,
                    'product_id' => $product->id,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'type' => 'in',
                    'quantity' => $detail->quantity,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'notes' => "Pembelian dari supplier - {$purchase->purchase_number}",
                    'created_by' => auth()->id() ?? null,
                ]);

                Log::info("Stock updated for product {$product->name}: +{$detail->quantity} units from purchase {$purchase->purchase_number}");
            }
        }
    }

    /**
     * Kembalikan stok produk (kurangi stok)
     */
    private function revertProductStock(Purchase $purchase): void
    {
        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $oldStock = $product->current_stock;
                $newStock = max(0, $product->current_stock - $detail->quantity);

                Product::withoutEvents(function () use ($product, $newStock) {
                    $product->update(['current_stock' => $newStock]);
                });

                StockMovementLog::create([
                    'cooperation_id' => $purchase->cooperation_id,
                    'product_id' => $product->id,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'type' => 'out',
                    'quantity' => $detail->quantity,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'notes' => "Pembatalan pembelian - {$purchase->purchase_number}",
                    'created_by' => auth()->id() ?? null,
                ]);

                Log::info("Stock reverted for product {$product->name}: -{$detail->quantity} units from purchase {$purchase->purchase_number}");
            }
        }
    }
}

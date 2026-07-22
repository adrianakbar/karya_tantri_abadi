<?php

namespace App\Observers;

use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\Log;

class PurchaseDetailObserver
{
    /**
     * Handle the PurchaseDetail "created" event.
     */
    public function created(PurchaseDetail $detail): void
    {
        if ($detail->purchase && $detail->purchase->status === 'received') {
            $this->updateStockAndLog($detail);
        }
    }

    /**
     * Update stock and create movement log
     */
    private function updateStockAndLog(PurchaseDetail $detail): void
    {
        $product = Product::find($detail->product_id);
        if (!$product) {
            return;
        }

        $oldStock = $product->current_stock;

        Product::withoutEvents(function () use ($product, $detail) {
            $product->increment('current_stock', $detail->quantity);
        });

        $newStock = $product->fresh()->current_stock;

        StockMovementLog::create([
            'cooperation_id' => $detail->purchase->cooperation_id,
            'product_id' => $product->id,
            'reference_type' => get_class($detail->purchase),
            'reference_id' => $detail->purchase->id,
            'type' => 'in',
            'quantity' => $detail->quantity,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => "Pembelian dari supplier - {$detail->purchase->purchase_number}",
            'created_by' => auth()->id() ?? null,
        ]);

        Log::info("Stock updated via PurchaseDetail creation for product {$product->name}: +{$detail->quantity} units");
    }
}

<?php

namespace App\Observers;

use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\Log;

class SaleDetailObserver
{
    /**
     * Handle the SaleDetail "created" event.
     */
    public function created(SaleDetail $detail): void
    {
        if ($detail->sale && $detail->sale->status === 'completed') {
            $this->updateStockAndLog($detail);
        }
    }

    /**
     * Update stock and create movement log
     */
    private function updateStockAndLog(SaleDetail $detail): void
    {
        $product = Product::find($detail->product_id);
        if (!$product) {
            return;
        }

        $oldStock = $product->current_stock;
        $newStock = max(0, $product->current_stock - $detail->quantity);

        Product::withoutEvents(function () use ($product, $newStock) {
            $product->update(['current_stock' => $newStock]);
        });

        StockMovementLog::create([
            'cooperation_id' => $detail->sale->cooperation_id,
            'product_id' => $product->id,
            'reference_type' => get_class($detail->sale),
            'reference_id' => $detail->sale->id,
            'type' => 'out',
            'quantity' => $detail->quantity,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => "Penjualan produk - {$detail->sale->sale_number}",
            'created_by' => auth()->id() ?? null,
        ]);

        Log::info("Stock updated via SaleDetail creation for product {$product->name}: -{$detail->quantity} units");

        if ($newStock <= $product->min_stock) {
            Log::warning("Low stock alert for product {$product->name}: current stock {$newStock}, minimum stock {$product->min_stock}");
        }
    }
}

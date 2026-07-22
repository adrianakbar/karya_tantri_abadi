<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockMovementLog;
use App\Jobs\SendLowStockNotificationJob;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if current_stock was changed
        if ($product->wasChanged('current_stock')) {
            $oldStock = $product->getOriginal('current_stock');
            $newStock = $product->current_stock;
            $difference = $newStock - $oldStock;

            Log::info('Product stock updated', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'min_stock' => $product->min_stock,
                'difference' => $difference,
            ]);

            $this->createStockMovementLog($product, $oldStock, $newStock, $difference);

            // If stock becomes low or out of stock, dispatch notification job
            if ($newStock < $product->min_stock && $oldStock >= $product->min_stock) {
                SendLowStockNotificationJob::dispatch($product->cooperation_id);
            }
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Create initial stock log if product has initial stock
        if ($product->current_stock > 0) {
            $this->createStockMovementLog(
                $product,
                0,
                $product->current_stock,
                $product->current_stock,
                'Initial stock when product created'
            );
        }

        // Check if new product is created with low stock
        if ($product->current_stock < $product->min_stock) {
            SendLowStockNotificationJob::dispatch($product->cooperation_id);
        }
    }

    /**
     * Create stock movement log entry
     */
    private function createStockMovementLog(
        Product $product,
        int $oldStock,
        int $newStock,
        int $difference,
        string $notes = null
    ): void {
        try {
            // Determine type based on difference
            $type = 'adjustment';
            if ($difference > 0) {
                $type = 'in';
            } elseif ($difference < 0) {
                $type = 'out';
            }

            // Auto-generate notes if not provided
            if (!$notes) {
                if ($difference > 0) {
                    $notes = "Stock ditambah sebanyak " . abs($difference) . " unit";
                } elseif ($difference < 0) {
                    $notes = "Stock dikurangi sebanyak " . abs($difference) . " unit";
                } else {
                    $notes = "Stock adjustment (tidak ada perubahan kuantitas)";
                }
            }

            StockMovementLog::create([
                'cooperation_id' => $product->cooperation_id,
                'product_id' => $product->id,
                'reference_type' => 'manual_update',
                'reference_id' => null,
                'type' => $type,
                'quantity' => abs($difference),
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'notes' => $notes,
                'created_by' => auth()->id() ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create stock movement log: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'difference' => $difference,
            ]);
        }
    }
}

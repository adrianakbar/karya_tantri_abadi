<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovementLog;
use Illuminate\Support\Facades\Log;

class StockMovementService
{
    /**
     * Record stock movement from various sources
     */
    public static function recordMovement(
        int $productId,
        string $type,
        int $quantity,
        string $referenceType = null,
        int $referenceId = null,
        string $notes = null,
        int $createdBy = null
    ): bool {
        try {
            $product = Product::findOrFail($productId);
            $oldStock = $product->current_stock;
            
            // Calculate new stock based on type
            $newStock = $oldStock;
            switch ($type) {
                case 'in':
                case 'purchase':
                case 'return':
                    $newStock = $oldStock + $quantity;
                    break;
                case 'out':
                case 'sale':
                case 'damaged':
                    $newStock = $oldStock - $quantity;
                    break;
                case 'adjustment':
                    // For adjustment, quantity is the final stock value
                    $newStock = $quantity;
                    $quantity = abs($newStock - $oldStock);
                    break;
            }

            // Ensure stock doesn't go negative
            $newStock = max(0, $newStock);

            // Update product stock
            $product->update(['current_stock' => $newStock]);

            // Create log entry
            StockMovementLog::create([
                'cooperation_id' => $product->cooperation_id,
                'product_id' => $productId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'notes' => $notes ?? self::generateDefaultNotes($type, $quantity, $referenceType, $referenceId),
                'created_by' => $createdBy ?? auth()->id(),
            ]);

            Log::info('Stock movement recorded', [
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'reference' => $referenceType . '#' . $referenceId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to record stock movement: ' . $e->getMessage(), [
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
            
            return false;
        }
    }

    /**
     * Record multiple stock movements (for bulk operations)
     */
    public static function recordBulkMovements(array $movements): array
    {
        $results = [];
        
        foreach ($movements as $movement) {
            $result = self::recordMovement(
                $movement['product_id'],
                $movement['type'],
                $movement['quantity'],
                $movement['reference_type'] ?? null,
                $movement['reference_id'] ?? null,
                $movement['notes'] ?? null,
                $movement['created_by'] ?? null
            );
            
            $results[] = [
                'product_id' => $movement['product_id'],
                'success' => $result
            ];
        }
        
        return $results;
    }

    /**
     * Generate default notes based on movement type
     */
    private static function generateDefaultNotes(
        string $type, 
        int $quantity, 
        string $referenceType = null, 
        int $referenceId = null
    ): string {
        $typeLabels = [
            'in' => 'Penambahan stok',
            'out' => 'Pengurangan stok',
            'purchase' => 'Pembelian',
            'sale' => 'Penjualan',
            'return' => 'Retur',
            'damaged' => 'Rusak/Hilang',
            'adjustment' => 'Penyesuaian stok',
        ];

        $action = $typeLabels[$type] ?? ucfirst($type);
        $notes = "{$action} sebanyak {$quantity} unit";

        if ($referenceType && $referenceId) {
            $notes .= " (Ref: {$referenceType} #{$referenceId})";
        }

        return $notes;
    }

    /**
     * Get stock movement summary for a product
     */
    public static function getProductStockSummary(int $productId, int $days = 30): array
    {
        $movements = StockMovementLog::where('product_id', $productId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $totalIn = $movements->whereIn('type', ['in', 'purchase', 'return'])->sum('quantity');
        $totalOut = $movements->whereIn('type', ['out', 'sale', 'damaged'])->sum('quantity');
        $adjustments = $movements->where('type', 'adjustment')->count();

        return [
            'total_movements' => $movements->count(),
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_change' => $totalIn - $totalOut,
            'adjustments' => $adjustments,
            'period_days' => $days,
        ];
    }

    /**
     * Get low stock products after recent movements
     */
    public static function getRecentLowStockProducts(int $hours = 24): array
    {
        return StockMovementLog::where('created_at', '>=', now()->subHours($hours))
            ->whereHas('product', function ($query) {
                $query->whereColumn('stock_movement_logs.stock_after', '<', 'products.min_stock');
            })
            ->with('product')
            ->get()
            ->unique('product_id')
            ->map(function ($log) {
                return [
                    'product' => $log->product,
                    'current_stock' => $log->stock_after,
                    'min_stock' => $log->product->min_stock,
                    'last_movement' => $log->created_at,
                    'movement_type' => $log->type,
                ];
            })
            ->values()
            ->toArray();
    }
}

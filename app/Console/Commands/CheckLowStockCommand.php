<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStockCommand extends Command
{
    protected $signature = 'products:check-low-stock {--cooperation-id=}';
    protected $description = 'Check for products with low stock and log alerts';

    public function handle()
    {
        $cooperationId = $this->option('cooperation-id');
        
        if (!$cooperationId) {
            $this->error('Please provide cooperation-id option');
            return 1;
        }

        $lowStockProducts = Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->where('current_stock', '>', 0)
            ->get();

        $outOfStockProducts = Product::where('cooperation_id', $cooperationId)
            ->where('current_stock', '<=', 0)
            ->get();

        if ($outOfStockProducts->count() > 0) {
            $this->error("🚨 STOK HABIS - {$outOfStockProducts->count()} produk:");
            foreach ($outOfStockProducts as $product) {
                $this->line("  - {$product->name} (Stok: {$product->current_stock})");
            }
            $this->newLine();
        }

        if ($lowStockProducts->count() > 0) {
            $this->warn("⚠️  STOK RENDAH - {$lowStockProducts->count()} produk:");
            foreach ($lowStockProducts as $product) {
                $this->line("  - {$product->name} (Stok: {$product->current_stock}, Min: {$product->min_stock})");
            }
            $this->newLine();
        }

        if ($lowStockProducts->count() === 0 && $outOfStockProducts->count() === 0) {
            $this->info("✅ Semua produk memiliki stok yang cukup!");
        }

        // Log untuk keperluan monitoring
        if ($lowStockProducts->count() > 0 || $outOfStockProducts->count() > 0) {
            Log::warning('Low stock alert', [
                'cooperation_id' => $cooperationId,
                'low_stock_count' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockProducts->count(),
                'low_stock_products' => $lowStockProducts->pluck('name')->toArray(),
                'out_of_stock_products' => $outOfStockProducts->pluck('name')->toArray(),
            ]);
        }

        return 0;
    }
}

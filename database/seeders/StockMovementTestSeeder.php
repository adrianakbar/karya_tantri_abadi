<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovementLog;
use App\Models\Cooperation;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockMovementTestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ambil cooperation dan user pertama
        $cooperation = Cooperation::first();
        $user = User::where('cooperation_id', $cooperation->id)->first();
        
        if (!$cooperation || !$user) {
            $this->command->error('No cooperation or user found. Please create them first.');
            return;
        }

        // Ambil atau buat produk untuk testing
        $products = Product::where('cooperation_id', $cooperation->id)->take(3)->get();
        
        if ($products->count() < 3) {
            $this->command->error('Need at least 3 products. Run ProductLowStockTestSeeder first.');
            return;
        }

        $this->command->info('Creating stock movement test data...');

        // Simulasi perubahan stok selama 30 hari terakhir
        $startDate = now()->subDays(30);
        
        foreach ($products as $product) {
            $this->createMovementsForProduct($product, $startDate, $user->id);
        }

        $this->command->info('Stock movement test data created successfully!');
        $this->command->line('');
        $this->command->info('Generated movements:');
        $this->command->line('- Initial stock entries');
        $this->command->line('- Purchase transactions');
        $this->command->line('- Sale transactions');
        $this->command->line('- Stock adjustments');
        $this->command->line('- Damaged/lost items');
        $this->command->line('- Return transactions');
    }

    private function createMovementsForProduct(Product $product, Carbon $startDate, int $userId): void
    {
        $currentDate = $startDate->copy();
        $currentStock = 0;

        // 1. Initial stock (30 hari lalu)
        $initialStock = rand(50, 100);
        $this->createMovement($product, 'in', $initialStock, $currentDate, $userId, 'initial_stock', null, 'Stok awal produk');
        $currentStock = $initialStock;

        // 2. Simulasi transaksi selama 30 hari
        for ($day = 0; $day < 30; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Random chance untuk berbagai jenis transaksi
            $dailyTransactions = rand(0, 3);
            
            for ($i = 0; $i < $dailyTransactions; $i++) {
                $transactionType = $this->getRandomTransactionType();
                $quantity = $this->getRandomQuantity($transactionType);
                
                // Pastikan stok tidak minus untuk transaksi keluar
                if (in_array($transactionType, ['out', 'sale', 'damaged']) && $quantity > $currentStock) {
                    $quantity = max(1, floor($currentStock / 2));
                }
                
                if ($quantity > 0) {
                    $referenceId = rand(1000, 9999);
                    $this->createMovement(
                        $product, 
                        $transactionType, 
                        $quantity, 
                        $date->addHours(rand(8, 18)), 
                        $userId,
                        $this->getReferenceType($transactionType),
                        $referenceId
                    );
                    
                    // Update current stock untuk tracking
                    if (in_array($transactionType, ['in', 'purchase', 'return'])) {
                        $currentStock += $quantity;
                    } else {
                        $currentStock = max(0, $currentStock - $quantity);
                    }
                }
            }
        }

        // 3. Adjustment hari ini jika stok berbeda dengan database
        $actualStock = $product->current_stock;
        if ($actualStock != $currentStock) {
            $this->createMovement(
                $product, 
                'adjustment', 
                $actualStock, 
                now(), 
                $userId,
                'manual_adjustment',
                null,
                "Penyesuaian stok dari {$currentStock} ke {$actualStock}"
            );
        }
    }

    private function createMovement(
        Product $product, 
        string $type, 
        int $quantity, 
        Carbon $date, 
        int $userId,
        string $referenceType = null,
        int $referenceId = null,
        string $notes = null
    ): void {
        // Calculate stock before/after
        $stockBefore = $product->current_stock;
        $stockAfter = $stockBefore;
        
        switch ($type) {
            case 'in':
            case 'purchase':
            case 'return':
                $stockAfter = $stockBefore + $quantity;
                break;
            case 'out':
            case 'sale':
            case 'damaged':
                $stockAfter = max(0, $stockBefore - $quantity);
                break;
            case 'adjustment':
                $stockAfter = $quantity;
                $quantity = abs($stockAfter - $stockBefore);
                break;
        }

        // Create log entry with custom date
        StockMovementLog::create([
            'cooperation_id' => $product->cooperation_id,
            'product_id' => $product->id,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes ?? $this->generateNotes($type, $quantity, $referenceType, $referenceId),
            'created_by' => $userId,
            'created_at' => $date,
            // Remove updated_at since model has UPDATED_AT = null
        ]);

        // Don't update product stock here - it will be handled by the final adjustment
    }

    private function getRandomTransactionType(): string
    {
        $types = [
            'purchase' => 30,   // 30% chance
            'sale' => 40,       // 40% chance
            'in' => 10,         // 10% chance
            'out' => 10,        // 10% chance
            'return' => 5,      // 5% chance
            'damaged' => 5,     // 5% chance
        ];

        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($types as $type => $chance) {
            $cumulative += $chance;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'sale';
    }

    private function getRandomQuantity(string $type): int
    {
        switch ($type) {
            case 'purchase':
                return rand(10, 50);
            case 'sale':
                return rand(1, 15);
            case 'in':
                return rand(5, 20);
            case 'out':
                return rand(1, 10);
            case 'return':
                return rand(1, 5);
            case 'damaged':
                return rand(1, 3);
            default:
                return rand(1, 10);
        }
    }

    private function getReferenceType(string $type): string
    {
        $mapping = [
            'purchase' => 'purchase_order',
            'sale' => 'sales_order',
            'in' => 'stock_in',
            'out' => 'stock_out',
            'return' => 'return_order',
            'damaged' => 'damage_report',
        ];

        return $mapping[$type] ?? 'manual';
    }

    private function generateNotes(string $type, int $quantity, string $referenceType = null, int $referenceId = null): string
    {
        $typeLabels = [
            'in' => 'Penambahan stok',
            'out' => 'Pengurangan stok',
            'purchase' => 'Pembelian dari supplier',
            'sale' => 'Penjualan ke customer',
            'return' => 'Retur dari customer',
            'damaged' => 'Barang rusak/hilang',
            'adjustment' => 'Penyesuaian stok',
        ];

        $action = $typeLabels[$type] ?? ucfirst($type);
        $notes = "{$action} sebanyak {$quantity} unit";

        if ($referenceType && $referenceId) {
            $notes .= " (Ref: {$referenceType} #{$referenceId})";
        }

        return $notes;
    }
}

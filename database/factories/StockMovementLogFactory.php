<?php

namespace Database\Factories;

use App\Models\StockMovementLog;
use App\Models\Product;
use App\Models\Cooperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovementLog>
 */
class StockMovementLogFactory extends Factory
{
    protected $model = StockMovementLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['purchase', 'sale', 'in', 'out', 'return', 'damaged', 'adjustment'];
        $type = $this->faker->randomElement($types);
        
        $referenceTypes = [
            'purchase' => 'purchase_order',
            'sale' => 'sales_order',
            'in' => 'stock_in',
            'out' => 'stock_out',
            'return' => 'return_order',
            'damaged' => 'damage_report',
            'adjustment' => 'manual_adjustment'
        ];

        $stockBefore = $this->faker->numberBetween(0, 200);
        $quantity = $this->faker->numberBetween(1, 50);
        
        // Calculate stock after based on type
        $stockAfter = $stockBefore;
        if (in_array($type, ['purchase', 'in', 'return'])) {
            $stockAfter = $stockBefore + $quantity;
        } elseif (in_array($type, ['sale', 'out', 'damaged'])) {
            $stockAfter = max(0, $stockBefore - $quantity);
        } elseif ($type === 'adjustment') {
            $stockAfter = $this->faker->numberBetween(0, 300);
            $quantity = abs($stockAfter - $stockBefore);
        }

        return [
            'cooperation_id' => Cooperation::factory(),
            'product_id' => Product::factory(),
            'reference_type' => $referenceTypes[$type] ?? 'manual',
            'reference_id' => $this->faker->optional(0.8)->numberBetween(1, 1000),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $this->generateNotes($type, $quantity),
            'created_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Generate realistic notes based on movement type
     */
    private function generateNotes(string $type, int $quantity): string
    {
        $notes = [
            'purchase' => [
                "Pembelian {$quantity} unit dari supplier",
                "Purchase order - {$quantity} items received",
                "Restocking dari vendor utama"
            ],
            'sale' => [
                "Penjualan {$quantity} unit ke customer",
                "Transaksi penjualan retail",
                "Order delivery completed"
            ],
            'in' => [
                "Penambahan stok manual {$quantity} unit",
                "Stock adjustment - increase",
                "Tambah stok dari transfer gudang"
            ],
            'out' => [
                "Pengurangan stok manual {$quantity} unit",
                "Stock adjustment - decrease",
                "Transfer ke cabang lain"
            ],
            'return' => [
                "Retur customer {$quantity} unit",
                "Barang dikembalikan - defect",
                "Customer return - wrong item"
            ],
            'damaged' => [
                "Barang rusak {$quantity} unit",
                "Damaged during transport",
                "Expired items removed"
            ],
            'adjustment' => [
                "Penyesuaian stok hasil stocktake",
                "Physical count adjustment",
                "Koreksi selisih stok"
            ]
        ];

        return $this->faker->randomElement($notes[$type] ?? ["Stock movement - {$quantity} units"]);
    }

    /**
     * Create a purchase movement
     */
    public function purchase(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $this->faker->numberBetween(0, 100);
            $quantity = $this->faker->numberBetween(10, 50);
            
            return [
                'type' => 'purchase',
                'reference_type' => 'purchase_order',
                'reference_id' => $this->faker->numberBetween(1, 1000),
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $quantity,
                'notes' => "Pembelian {$quantity} unit dari supplier"
            ];
        });
    }

    /**
     * Create a sale movement
     */
    public function sale(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $this->faker->numberBetween(20, 100);
            $quantity = $this->faker->numberBetween(1, 20);
            
            return [
                'type' => 'sale',
                'reference_type' => 'sales_order',
                'reference_id' => $this->faker->numberBetween(1, 1000),
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => max(0, $stockBefore - $quantity),
                'notes' => "Penjualan {$quantity} unit ke customer"
            ];
        });
    }

    /**
     * Create a damaged movement
     */
    public function damaged(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $this->faker->numberBetween(10, 100);
            $quantity = $this->faker->numberBetween(1, 10);
            
            return [
                'type' => 'damaged',
                'reference_type' => 'damage_report',
                'reference_id' => $this->faker->numberBetween(1, 100),
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => max(0, $stockBefore - $quantity),
                'notes' => "Barang rusak {$quantity} unit"
            ];
        });
    }

    /**
     * Create a stock adjustment movement
     */
    public function adjustment(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $this->faker->numberBetween(0, 100);
            $stockAfter = $this->faker->numberBetween(0, 150);
            $quantity = abs($stockAfter - $stockBefore);
            
            return [
                'type' => 'adjustment',
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Penyesuaian stok dari {$stockBefore} ke {$stockAfter}"
            ];
        });
    }

    /**
     * Create a return movement
     */
    public function return(): static
    {
        return $this->state(function (array $attributes) {
            $stockBefore = $this->faker->numberBetween(0, 100);
            $quantity = $this->faker->numberBetween(1, 10);
            
            return [
                'type' => 'return',
                'reference_type' => 'return_order',
                'reference_id' => $this->faker->numberBetween(1, 500),
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $quantity,
                'notes' => "Retur customer {$quantity} unit"
            ];
        });
    }

    /**
     * Create movements for recent dates
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }

    /**
     * Create movements for today
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('today', 'now'),
            ];
        });
    }

    /**
     * Create movements for this month
     */
    public function thisMonth(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('first day of this month', 'now'),
            ];
        });
    }

    /**
     * Create movements with specific cooperation
     */
    public function forCooperation(Cooperation $cooperation): static
    {
        return $this->state(function (array $attributes) use ($cooperation) {
            return [
                'cooperation_id' => $cooperation->id,
            ];
        });
    }

    /**
     * Create movements with specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'cooperation_id' => $product->cooperation_id,
                'product_id' => $product->id,
            ];
        });
    }

    /**
     * Create movements with specific user
     */
    public function byUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'cooperation_id' => $user->cooperation_id,
                'created_by' => $user->id,
            ];
        });
    }
}

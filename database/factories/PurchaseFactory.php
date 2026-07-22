<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Cooperation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 50000, 1000000);
        $discountAmount = fake()->randomFloat(2, 0, $totalAmount * 0.05);
        $taxAmount = ($totalAmount - $discountAmount) * 0.1; // 10% tax
        $grandTotal = $totalAmount - $discountAmount + $taxAmount;

        return [
            'cooperation_id' => Cooperation::factory(),
            'supplier_id' => Supplier::factory(),
            'purchase_number' => 'PO-' . fake()->unique()->numerify('######'),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('######'),
            'purchase_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'total_amount' => $totalAmount,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'processed_by' => User::factory(),
            'status' => fake()->randomElement(['pending', 'received', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}

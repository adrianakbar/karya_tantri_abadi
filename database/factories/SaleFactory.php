<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Cooperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10000, 500000);
        $discountAmount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $taxAmount = ($subtotal - $discountAmount) * 0.1; // 10% tax
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'cooperation_id' => Cooperation::factory(),
            'customer_id' => User::factory(),
            'sale_number' => 'SL-' . fake()->unique()->numerify('######'),
            'sale_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_method' => fake()->randomElement(['cash', 'credit']),
            'processed_by' => User::factory(),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
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

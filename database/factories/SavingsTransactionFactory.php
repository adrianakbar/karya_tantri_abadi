<?php

namespace Database\Factories;

use App\Models\SavingsTransaction;
use App\Models\Cooperation;
use App\Models\User;
use App\Models\SavingsType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsTransactionFactory extends Factory
{
    protected $model = SavingsTransaction::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'user_id' => User::factory(),
            'savings_type_id' => SavingsType::factory(),
            'transaction_number' => 'SV-' . fake()->unique()->numerify('######'),
            'amount' => fake()->randomFloat(2, 50000, 1000000),
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->optional()->sentence(),
            'receipt_number' => fake()->optional()->numerify('RCP-######'),
            'processed_by' => User::factory(),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
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

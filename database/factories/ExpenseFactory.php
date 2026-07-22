<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Cooperation;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'expense_number' => 'EXP-' . fake()->unique()->numerify('######'),
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'receipt_number' => fake()->optional()->numerify('RCP-######'),
            'recipient' => fake()->name(),
            'processed_by' => User::factory(),
            'approved_by' => User::factory(),
            'status' => fake()->randomElement(['pending', 'approved', 'paid']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}

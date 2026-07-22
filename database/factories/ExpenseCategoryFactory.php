<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'name' => fake()->randomElement(['Office Supplies', 'Utilities', 'Marketing', 'Travel', 'Maintenance', 'Insurance', 'Professional Services']),
            'code' => fake()->unique()->regexify('[A-Z]{3}[0-9]{2}'),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

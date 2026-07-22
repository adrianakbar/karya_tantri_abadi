<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'name' => fake()->randomElement(['Electronics', 'Food & Beverage', 'Clothing', 'Books', 'Health & Beauty', 'Home & Garden']),
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

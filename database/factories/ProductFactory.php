<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Cooperation;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->words(2, true),
            'code' => fake()->unique()->regexify('[A-Z0-9]{8}'),
            'barcode' => fake()->unique()->ean13(),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement(['pcs', 'kg', 'liter', 'box', 'pack']),
            'purchase_price' => fake()->randomFloat(2, 1000, 50000),
            'selling_price' => fake()->randomFloat(2, 1500, 75000),
            'min_stock' => fake()->numberBetween(5, 20),
            'current_stock' => fake()->numberBetween(0, 100),
            'image_url' => fake()->imageUrl(400, 400, 'products'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => fake()->numberBetween(0, 5),
        ]);
    }
}

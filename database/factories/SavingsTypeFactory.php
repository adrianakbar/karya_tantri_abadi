<?php

namespace Database\Factories;

use App\Models\SavingsType;
use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsTypeFactory extends Factory
{
    protected $model = SavingsType::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'name' => fake()->randomElement(['Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela', 'Simpanan Berjangka']),
            'code' => fake()->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'amount' => fake()->randomFloat(2, 25000, 500000),
            'is_mandatory' => fake()->boolean(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function mandatory(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => true,
        ]);
    }

    public function voluntary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

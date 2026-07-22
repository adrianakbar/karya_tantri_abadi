<?php

namespace Database\Factories;

use App\Models\LoanType;
use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanTypeFactory extends Factory
{
    protected $model = LoanType::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'name' => fake()->randomElement(['Pinjaman Multi Guna', 'Pinjaman Pendidikan', 'Pinjaman Modal Usaha']),
            'max_amount' => fake()->randomFloat(2, 5000000, 50000000),
            'interest_rate' => fake()->randomFloat(2, 1, 5),
            'max_tenor_months' => fake()->randomElement([6, 12, 24, 36]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

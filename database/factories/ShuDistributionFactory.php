<?php

namespace Database\Factories;

use App\Models\ShuDistribution;
use App\Models\Cooperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShuDistributionFactory extends Factory
{
    protected $model = ShuDistribution::class;

    public function definition(): array
    {
        $revenue = fake()->randomFloat(2, 50000000, 100000000);
        $expenses = fake()->randomFloat(2, 20000000, 40000000);
        $shu = $revenue - $expenses;

        return [
            'cooperation_id' => Cooperation::factory(),
            'year' => fake()->unique()->numberBetween(2020, 2026),
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'total_shu' => $shu,
            'distribution_date' => now(),
            'status' => 'distributed',
            'calculated_by' => User::factory(),
            'distributed_by' => User::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}

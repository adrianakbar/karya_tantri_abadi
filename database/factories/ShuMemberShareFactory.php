<?php

namespace Database\Factories;

use App\Models\ShuMemberShare;
use App\Models\ShuDistribution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShuMemberShareFactory extends Factory
{
    protected $model = ShuMemberShare::class;

    public function definition(): array
    {
        return [
            'shu_distribution_id' => ShuDistribution::factory(),
            'user_id' => User::factory(),
            'savings_contribution' => fake()->randomFloat(2, 500000, 5000000),
            'transaction_contribution' => fake()->randomFloat(2, 100000, 2000000),
            'shu_amount' => fake()->randomFloat(2, 50000, 500000),
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;

class CooperationFactory extends Factory
{
    protected $model = Cooperation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Cooperative',
            'code' => fake()->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'chairman_name' => fake()->name(),
            'established_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'logo_url' => fake()->imageUrl(200, 200, 'business'),
            'theme_color' => fake()->hexColor(),
            'is_active' => true,
            'subscription_expired_at' => fake()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_expired_at' => fake()->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}

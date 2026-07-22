<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Cooperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'member_number' => fake()->unique()->regexify('M-[0-9]{5}'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => Hash::make('password'),
            'address' => fake()->address(),
            'birth_date' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'job' => fake()->jobTitle(),
            'join_date' => now(),
            'is_active' => true,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\LoanPayment;
use App\Models\Loan;
use App\Models\Cooperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanPaymentFactory extends Factory
{
    protected $model = LoanPayment::class;

    public function definition(): array
    {
        $principal = fake()->randomFloat(2, 200000, 500000);
        $interest = fake()->randomFloat(2, 10000, 50000);
        $total = $principal + $interest;

        return [
            'cooperation_id' => Cooperation::factory(),
            'loan_id' => Loan::factory(),
            'payment_number' => fake()->unique()->regexify('PAY-[0-9]{5}'),
            'installment_number' => fake()->numberBetween(1, 12),
            'due_date' => now(),
            'payment_date' => now(),
            'principal_amount' => $principal,
            'interest_amount' => $interest,
            'total_amount' => $total,
            'paid_amount' => $total,
            'penalty_amount' => 0.00,
            'processed_by' => User::factory(),
            'status' => 'paid',
            'notes' => fake()->sentence(),
        ];
    }
}

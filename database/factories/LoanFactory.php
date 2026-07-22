<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Cooperation;
use App\Models\User;
use App\Models\LoanType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $principal = fake()->randomFloat(2, 1000000, 10000000);
        $interestRate = fake()->randomFloat(2, 1, 5);
        $tenor = fake()->randomElement([6, 12, 24]);
        
        $totalInterest = $principal * ($interestRate / 100) * ($tenor / 12);
        $totalPayment = $principal + $totalInterest;
        $monthlyPayment = $totalPayment / $tenor;

        return [
            'cooperation_id' => Cooperation::factory(),
            'user_id' => User::factory(),
            'loan_type_id' => LoanType::factory(),
            'loan_number' => fake()->unique()->regexify('L-[0-9]{5}'),
            'principal_amount' => $principal,
            'interest_rate' => $interestRate,
            'tenor_months' => $tenor,
            'monthly_payment' => $monthlyPayment,
            'total_payment' => $totalPayment,
            'remaining_balance' => $totalPayment,
            'application_date' => now()->subMonths(1),
            'approved_date' => now()->subMonths(1)->addDays(2),
            'disbursement_date' => now()->subMonths(1)->addDays(3),
            'due_date' => now()->addMonths($tenor),
            'approved_by' => User::factory(),
            'purpose' => fake()->sentence(),
            'status' => 'active',
            'notes' => fake()->sentence(),
        ];
    }
}

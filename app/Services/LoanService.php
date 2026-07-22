<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanPayment;
use Carbon\Carbon;

class LoanService
{
    public function generatePaymentSchedule(Loan $loan): void
    {
        $loan->payments()->delete();

        $count = (int) ($loan->installment_count ?: $loan->tenor_months);
        if ($count <= 0) {
            return;
        }

        $principalPerInstallment = $loan->principal_amount / $count;
        $feePerInstallment = ($loan->installment_fee ?? 0) / $count;
        $installmentAmount = $loan->monthly_payment;

        $startDate = $loan->disbursement_date
            ? Carbon::parse($loan->disbursement_date)
            : Carbon::parse($loan->application_date);

        $frequency = $loan->payment_frequency ?: 'weekly';

        for ($i = 1; $i <= $count; $i++) {
            $dueDate = $frequency === 'weekly'
                ? $startDate->copy()->addWeeks($i)
                : $startDate->copy()->addMonths($i);

            LoanPayment::create([
                'cooperation_id' => $loan->cooperation_id,
                'loan_id' => $loan->id,
                'payment_number' => $loan->loan_number . '/' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'installment_number' => $i,
                'due_date' => $dueDate,
                'principal_amount' => round($principalPerInstallment, 2),
                'interest_amount' => round($feePerInstallment, 2),
                'total_amount' => $installmentAmount,
                'status' => 'pending',
            ]);
        }
    }
}

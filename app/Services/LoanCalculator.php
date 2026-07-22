<?php

namespace App\Services;

/**
 * Kalkulasi pinjaman kelompok Karya Tantri Abadi.
 *
 * - Plafon max: Rp 5.000.000
 * - Tenor max: 3 bulan (bisa mingguan)
 * - Biaya angsuran: 11% dari nominal
 * - Admin: 5% dari nominal
 * - UTJ: 22% dari nominal
 * - Cair bersih: 73% dari nominal (admin 5% + UTJ 22% = 27%)
 */
class LoanCalculator
{
    public const MAX_PRINCIPAL = 5_000_000;
    public const MAX_TENOR_MONTHS = 3;
    public const INSTALLMENT_FEE_RATE = 0.11; // 11%
    public const ADMIN_FEE_RATE = 0.05;       // 5%
    public const UTJ_FEE_RATE = 0.22;         // 22%
    public const NET_DISBURSEMENT_RATE = 0.73; // 73%

    public static function calculate(float $principal, int $tenorMonths, string $frequency = 'weekly'): array
    {
        $principal = max(0, $principal);
        $tenorMonths = max(1, min(self::MAX_TENOR_MONTHS, $tenorMonths));
        $frequency = in_array($frequency, ['weekly', 'monthly'], true) ? $frequency : 'weekly';

        $adminFee = round($principal * self::ADMIN_FEE_RATE, 2);
        $utjFee = round($principal * self::UTJ_FEE_RATE, 2);
        $installmentFee = round($principal * self::INSTALLMENT_FEE_RATE, 2);
        $netDisbursement = round($principal * self::NET_DISBURSEMENT_RATE, 2);

        // Total yang harus dilunasi = nominal + biaya angsuran 11%
        $totalPayment = round($principal + $installmentFee, 2);

        $installmentCount = $frequency === 'weekly'
            ? $tenorMonths * 4
            : $tenorMonths;

        $installmentAmount = $installmentCount > 0
            ? round($totalPayment / $installmentCount, 2)
            : 0;

        return [
            'principal_amount' => $principal,
            'admin_fee' => $adminFee,
            'utj_fee' => $utjFee,
            'installment_fee' => $installmentFee,
            'net_disbursement' => $netDisbursement,
            'interest_rate' => self::INSTALLMENT_FEE_RATE * 100,
            'tenor_months' => $tenorMonths,
            'payment_frequency' => $frequency,
            'installment_count' => $installmentCount,
            'monthly_payment' => $installmentAmount, // dipakai juga utk angsuran mingguan
            'total_payment' => $totalPayment,
            'remaining_balance' => $totalPayment,
        ];
    }
}

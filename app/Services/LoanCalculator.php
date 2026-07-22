<?php

namespace App\Services;

/**
 * Kalkulasi pinjaman kelompok Karya Tantri Abadi.
 *
 * Sumber: tabel plafond mitra (plafon 500rb–5jt).
 *
 * - Plafon max: Rp 5.000.000
 * - Tenor max: 3 bulan (bisa mingguan)
 * - Biaya angsuran: 11% dari nominal (total fee dilunasi, terpisah dari potongan cair)
 * - Admin: 5% dari nominal
 * - UTJ tier:
 *     - plafon ≤ 2.500.000 → 22% (cair bersih 73%)
 *     - plafon ≥ 2.600.000 → 11% (cair bersih 84%)
 * - Cair bersih = nominal − admin − UTJ (angsuran 11% tidak dipotong di awal)
 */
class LoanCalculator
{
    public const MAX_PRINCIPAL = 5_000_000;
    public const MAX_TENOR_MONTHS = 3;
    public const INSTALLMENT_FEE_RATE = 0.11; // 11%
    public const ADMIN_FEE_RATE = 0.05;       // 5%

    /** Plafon ≤ threshold: UTJ 22% / cair 73%. Di atas threshold: UTJ 11% / cair 84%. */
    public const UTJ_TIER_THRESHOLD = 2_500_000;
    public const UTJ_FEE_RATE_LOW = 0.22;     // ≤ 2.500.000
    public const UTJ_FEE_RATE_HIGH = 0.11;    // ≥ 2.600.000
    public const NET_DISBURSEMENT_RATE_LOW = 0.73;
    public const NET_DISBURSEMENT_RATE_HIGH = 0.84;

    /** @deprecated Gunakan utjFeeRate() — nilai default tier rendah (≤2,5jt). */
    public const UTJ_FEE_RATE = self::UTJ_FEE_RATE_LOW;

    /** @deprecated Gunakan netDisbursementRate() — nilai default tier rendah (≤2,5jt). */
    public const NET_DISBURSEMENT_RATE = self::NET_DISBURSEMENT_RATE_LOW;

    public static function utjFeeRate(float $principal): float
    {
        return $principal > self::UTJ_TIER_THRESHOLD
            ? self::UTJ_FEE_RATE_HIGH
            : self::UTJ_FEE_RATE_LOW;
    }

    public static function netDisbursementRate(float $principal): float
    {
        return $principal > self::UTJ_TIER_THRESHOLD
            ? self::NET_DISBURSEMENT_RATE_HIGH
            : self::NET_DISBURSEMENT_RATE_LOW;
    }

    public static function utjRatePercent(float $principal): int
    {
        return (int) round(self::utjFeeRate($principal) * 100);
    }

    public static function netRatePercent(float $principal): int
    {
        return (int) round(self::netDisbursementRate($principal) * 100);
    }

    /**
     * Ringkasan fee untuk deskripsi form/infolist.
     * Jika $principal null/0 → tampilkan kedua tier.
     */
    public static function feeDescription(?float $principal = null): string
    {
        if ($principal !== null && $principal > 0) {
            $utj = self::utjRatePercent($principal);
            $net = self::netRatePercent($principal);

            return "Angsuran 11% · Admin 5% · UTJ {$utj}% · Cair bersih {$net}%";
        }

        return 'Angsuran 11% · Admin 5% · UTJ 22% (≤2,5jt) / 11% (>2,5jt) · Cair 73% / 84%';
    }

    public static function calculate(float $principal, int $tenorMonths, string $frequency = 'weekly'): array
    {
        $principal = max(0, $principal);
        $tenorMonths = max(1, min(self::MAX_TENOR_MONTHS, $tenorMonths));
        $frequency = in_array($frequency, ['weekly', 'monthly'], true) ? $frequency : 'weekly';

        $utjRate = self::utjFeeRate($principal);

        $adminFee = round($principal * self::ADMIN_FEE_RATE, 2);
        $utjFee = round($principal * $utjRate, 2);
        $installmentFee = round($principal * self::INSTALLMENT_FEE_RATE, 2);
        // Cair bersih = potongan admin + UTJ saja (bukan angsuran)
        $netDisbursement = round($principal - $adminFee - $utjFee, 2);

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

<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\Roles;
use App\Models\UserRole;
use App\Models\Cooperation;
use App\Services\LoanCalculator;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PinjamanSeeder extends Seeder
{
    public function run(): void
    {
        $cooperation = Cooperation::first();
        if (!$cooperation) {
            $this->command->error('Tidak ada cooperation.');
            return;
        }

        // Pastikan jenis pinjaman tunggal: Kelompok
        LoanType::where('cooperation_id', $cooperation->id)
            ->where('name', '!=', 'Kelompok')
            ->update(['is_active' => false]);

        $kelompok = LoanType::updateOrCreate(
            ['cooperation_id' => $cooperation->id, 'name' => 'Kelompok'],
            [
                'max_amount' => LoanCalculator::MAX_PRINCIPAL,
                'interest_rate' => LoanCalculator::INSTALLMENT_FEE_RATE * 100,
                'max_tenor_months' => LoanCalculator::MAX_TENOR_MONTHS,
                'description' => 'Pinjaman kelompok — angsuran 11%, admin 5%, UTJ 22% (≤2,5jt) / 11% (>2,5jt), cair 73%/84%. Plafon max 5jt, tenor max 3 bulan (mingguan/bulanan).',
                'is_active' => true,
            ]
        );

        $anggotaRole = Roles::where('name', 'anggota')->where('cooperation_id', $cooperation->id)->first();
        $spvRole = Roles::where('name', 'spv')->where('cooperation_id', $cooperation->id)->first();
        $kasirRole = Roles::where('name', 'kasir')->where('cooperation_id', $cooperation->id)->first();

        if (!$anggotaRole) {
            $this->command->error('Role anggota tidak ditemukan.');
            return;
        }

        $anggotaIds = UserRole::where('role_id', $anggotaRole->id)->pluck('user_id')->toArray();
        $spvId = $spvRole ? UserRole::where('role_id', $spvRole->id)->value('user_id') : null;
        $kasirId = $kasirRole ? UserRole::where('role_id', $kasirRole->id)->value('user_id') : null;

        if (empty($anggotaIds)) {
            $this->command->error('Tidak ada user anggota.');
            return;
        }

        // Bersihkan pinjaman lama seeder
        LoanPayment::query()->delete();
        Loan::query()->delete();

        $samples = [
            [
                'user_id' => $anggotaIds[0],
                'principal' => 3_000_000,
                'tenor' => 3,
                'frequency' => 'weekly',
                'status' => 'pending',
                'purpose' => 'Modal usaha kelompok mingguan',
                'application_date' => now()->subDays(2),
            ],
            [
                'user_id' => $anggotaIds[array_key_exists(1, $anggotaIds) ? 1 : 0],
                'principal' => 2_000_000,
                'tenor' => 2,
                'frequency' => 'weekly',
                'status' => 'pending',
                'purpose' => 'Kebutuhan usaha bersama',
                'application_date' => now()->subDays(1),
            ],
            [
                'user_id' => $anggotaIds[array_key_exists(2, $anggotaIds) ? 2 : 0],
                'principal' => 5_000_000,
                'tenor' => 3,
                'frequency' => 'weekly',
                'status' => 'approved',
                'purpose' => 'Modal kerja kelompok',
                'application_date' => now()->subDays(10),
                'approved_date' => now()->subDays(7),
                'approved_by' => $spvId,
            ],
            [
                'user_id' => $anggotaIds[0],
                'principal' => 4_000_000,
                'tenor' => 3,
                'frequency' => 'weekly',
                'status' => 'active',
                'purpose' => 'Pengembangan usaha kelompok',
                'application_date' => now()->subWeeks(8),
                'approved_date' => now()->subWeeks(8)->addDays(2),
                'disbursement_date' => now()->subWeeks(8)->addDays(3),
                'approved_by' => $spvId,
                'payments_paid' => 4,
            ],
            [
                'user_id' => $anggotaIds[array_key_exists(1, $anggotaIds) ? 1 : 0],
                'principal' => 2_500_000,
                'tenor' => 2,
                'frequency' => 'monthly',
                'status' => 'active',
                'purpose' => 'Modal bulanan kelompok',
                'application_date' => now()->subMonths(2),
                'approved_date' => now()->subMonths(2)->addDays(1),
                'disbursement_date' => now()->subMonths(2)->addDays(2),
                'approved_by' => $spvId,
                'payments_paid' => 1,
            ],
            [
                'user_id' => $anggotaIds[array_key_exists(2, $anggotaIds) ? 2 : 0],
                'principal' => 1_500_000,
                'tenor' => 2,
                'frequency' => 'weekly',
                'status' => 'overdue',
                'purpose' => 'Pinjaman kelompok tertunda',
                'application_date' => now()->subWeeks(12),
                'approved_date' => now()->subWeeks(12)->addDays(1),
                'disbursement_date' => now()->subWeeks(12)->addDays(2),
                'approved_by' => $spvId,
                'payments_paid' => 2,
            ],
            [
                'user_id' => $anggotaIds[0],
                'principal' => 1_000_000,
                'tenor' => 1,
                'frequency' => 'weekly',
                'status' => 'completed',
                'purpose' => 'Pinjaman lunas',
                'application_date' => now()->subWeeks(8),
                'approved_date' => now()->subWeeks(8)->addDays(1),
                'disbursement_date' => now()->subWeeks(8)->addDays(2),
                'approved_by' => $spvId,
                'payments_paid' => 4, // 1 bulan x 4 minggu
            ],
        ];

        $loanService = new LoanService();
        $created = 0;

        foreach ($samples as $idx => $data) {
            $calc = LoanCalculator::calculate($data['principal'], $data['tenor'], $data['frequency']);
            $loanNumber = 'LOAN-' . now()->format('Ymd') . '-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $disburseDate = $data['disbursement_date'] ?? null;
            $paidCount = $data['payments_paid'] ?? 0;

            $remaining = $calc['total_payment'] - ($calc['monthly_payment'] * $paidCount);
            if ($data['status'] === 'completed') {
                $remaining = 0;
            }

            $loan = Loan::create(array_merge($calc, [
                'cooperation_id' => $cooperation->id,
                'user_id' => $data['user_id'],
                'loan_type_id' => $kelompok->id,
                'loan_number' => $loanNumber,
                'application_date' => $data['application_date'],
                'approved_date' => $data['approved_date'] ?? null,
                'disbursement_date' => $disburseDate,
                'due_date' => $disburseDate
                    ? Carbon::parse($disburseDate)->addMonths($data['tenor'])
                    : null,
                'approved_by' => $data['approved_by'] ?? null,
                'purpose' => $data['purpose'],
                'status' => $data['status'],
                'remaining_balance' => max(0, $remaining),
                'notes' => null,
            ]));

            if ($disburseDate) {
                $loanService->generatePaymentSchedule($loan);
                $payments = $loan->payments()->orderBy('installment_number')->get();
                foreach ($payments as $i => $payment) {
                    $n = $i + 1;
                    if ($n <= $paidCount) {
                        $payment->update([
                            'status' => 'paid',
                            'payment_date' => Carbon::parse($payment->due_date)->subDays(1),
                            'paid_amount' => $payment->total_amount,
                            'processed_by' => $kasirId,
                        ]);
                    } elseif ($payment->due_date->isPast() && $data['status'] === 'overdue') {
                        $payment->update([
                            'status' => 'overdue',
                            'penalty_amount' => round($payment->total_amount * 0.02, 2),
                        ]);
                    }
                }
            }

            $created++;
            $this->command->line(sprintf(
                '✅ [%s] %s — nominal Rp %s · cair bersih Rp %s · %s',
                $data['status'],
                $loanNumber,
                number_format($data['principal'], 0, ',', '.'),
                number_format($calc['net_disbursement'], 0, ',', '.'),
                $data['frequency'] === 'weekly' ? 'mingguan' : 'bulanan'
            ));
        }

        $this->command->info("\n📊 {$created} pinjaman kelompok di-seed.");
        $this->command->line('   Jenis: Kelompok | Plafon max 5jt | Tenor max 3 bln');
        $this->command->line('   Biaya: angsuran 11% · admin 5% · UTJ 22%/11% (tier 2,5jt) · cair 73%/84%');
    }
}

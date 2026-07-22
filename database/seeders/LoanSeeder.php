<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanType;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Roles;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Get first cooperation
        $cooperation = \App\Models\Cooperation::first();
        if (!$cooperation) {
            $this->command->warn('No cooperation found');
            return;
        }

        // Get anggota users
        $anggotaRole = Roles::where('name', 'anggota')->first();
        if (!$anggotaRole) {
            $this->command->warn('Role anggota not found');
            return;
        }

        $anggotaUserIds = UserRole::where('role_id', $anggotaRole->id)
            ->pluck('user_id')
            ->toArray();

        if (empty($anggotaUserIds)) {
            $this->command->warn('No anggota users found');
            return;
        }

        // Create loan types if not exist
        $loanTypes = [
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Pinjaman Usaha',
                'max_amount' => 10000000,
                'interest_rate' => 1.5, // 1.5% per bulan
                'max_tenor_months' => 24,
                'description' => 'Pinjaman untuk modal usaha anggota',
                'is_active' => true,
            ],
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Pinjaman Konsumtif',
                'max_amount' => 5000000,
                'interest_rate' => 2.0, // 2% per bulan
                'max_tenor_months' => 12,
                'description' => 'Pinjaman untuk kebutuhan konsumtif',
                'is_active' => true,
            ],
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Pinjaman Darurat',
                'max_amount' => 2000000,
                'interest_rate' => 1.0, // 1% per bulan
                'max_tenor_months' => 6,
                'description' => 'Pinjaman untuk kebutuhan darurat',
                'is_active' => true,
            ],
        ];

        foreach ($loanTypes as $type) {
            LoanType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        // Get created loan types
        $savedLoanTypes = LoanType::whereIn('name', ['Pinjaman Usaha', 'Pinjaman Konsumtif', 'Pinjaman Darurat'])->get();

        // Create loans for some anggota (not all)
        $selectedUsers = array_slice($anggotaUserIds, 0, min(count($anggotaUserIds), 3));
        
        // Get admin user for approved_by
        $adminUser = \App\Models\User::first();
        if (!$adminUser) {
            $this->command->warn('No admin user found');
            return;
        }

        foreach ($selectedUsers as $index => $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            // Create different type of loan for each user
            $loanType = $savedLoanTypes[$index % count($savedLoanTypes)];
            
            // Calculate loan details
            $principalAmounts = [1000000, 2000000, 3000000, 5000000];
            $tenorOptions = [6, 12, 18, 24];
            
            $principalAmount = $principalAmounts[array_rand($principalAmounts)];
            $tenor = $tenorOptions[array_rand($tenorOptions)];
            $monthlyInterestRate = $loanType->interest_rate / 100;
            
            // Simple calculation for monthly payment
            $monthlyPayment = ($principalAmount * (1 + ($monthlyInterestRate * $tenor))) / $tenor;
            $totalPayment = $monthlyPayment * $tenor;
            $remainingBalance = $totalPayment - ($monthlyPayment * rand(1, $tenor - 1)); // Some payments made
            
            $applicationDate = Carbon::now()->subMonths(rand(2, 8));
            $approvedDate = $applicationDate->copy()->addDays(rand(3, 10));
            $disbursementDate = $approvedDate->copy()->addDays(rand(1, 5));
            $dueDate = $disbursementDate->copy()->addMonths($tenor);

            Loan::create([
                'cooperation_id' => $cooperation->id,
                'user_id' => $userId,
                'loan_type_id' => $loanType->id,
                'loan_number' => 'LN-' . date('Y') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'principal_amount' => $principalAmount,
                'interest_rate' => $loanType->interest_rate,
                'tenor_months' => $tenor,
                'monthly_payment' => $monthlyPayment,
                'total_payment' => $totalPayment,
                'remaining_balance' => $remainingBalance,
                'application_date' => $applicationDate,
                'approved_date' => $approvedDate,
                'disbursement_date' => $disbursementDate,
                'due_date' => $dueDate,
                'approved_by' => $adminUser->id,
                'purpose' => $this->getLoanPurpose($loanType->name),
                'status' => 'active',
                'notes' => 'Pinjaman disetujui dan sudah dicairkan',
            ]);
        }

        // Create one completed loan
        if (count($anggotaUserIds) > 3) {
            $userId = $anggotaUserIds[3];
            $loanType = $savedLoanTypes->first();
            
            $principalAmount = 1500000;
            $tenor = 12;
            $monthlyInterestRate = $loanType->interest_rate / 100;
            $monthlyPayment = ($principalAmount * (1 + ($monthlyInterestRate * $tenor))) / $tenor;
            $totalPayment = $monthlyPayment * $tenor;
            
            $applicationDate = Carbon::now()->subMonths(18);
            $approvedDate = $applicationDate->copy()->addDays(5);
            $disbursementDate = $approvedDate->copy()->addDays(2);
            $dueDate = $disbursementDate->copy()->addMonths($tenor);

            Loan::create([
                'cooperation_id' => $cooperation->id,
                'user_id' => $userId,
                'loan_type_id' => $loanType->id,
                'loan_number' => 'LN-' . date('Y') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-CMP',
                'principal_amount' => $principalAmount,
                'interest_rate' => $loanType->interest_rate,
                'tenor_months' => $tenor,
                'monthly_payment' => $monthlyPayment,
                'total_payment' => $totalPayment,
                'remaining_balance' => 0,
                'application_date' => $applicationDate,
                'approved_date' => $approvedDate,
                'disbursement_date' => $disbursementDate,
                'due_date' => $dueDate,
                'approved_by' => $adminUser->id,
                'purpose' => 'Modal usaha warung kelontong',
                'status' => 'completed',
                'notes' => 'Pinjaman telah lunas',
            ]);
        }

        $this->command->info('Loans seeded successfully');
    }

    private function getLoanPurpose(string $loanTypeName): string
    {
        return match ($loanTypeName) {
            'Pinjaman Usaha' => 'Modal usaha ' . ['warung kelontong', 'bengkel', 'salon', 'toko online', 'catering'][array_rand(['warung kelontong', 'bengkel', 'salon', 'toko online', 'catering'])],
            'Pinjaman Konsumtif' => ['Renovasi rumah', 'Biaya pendidikan anak', 'Pembelian motor', 'Biaya kesehatan', 'Pernikahan'][array_rand(['Renovasi rumah', 'Biaya pendidikan anak', 'Pembelian motor', 'Biaya kesehatan', 'Pernikahan'])],
            'Pinjaman Darurat' => ['Biaya rumah sakit', 'Perbaikan rumah bocor', 'Kebutuhan mendesak', 'Biaya operasi'][array_rand(['Biaya rumah sakit', 'Perbaikan rumah bocor', 'Kebutuhan mendesak', 'Biaya operasi'])],
            default => 'Kebutuhan anggota',
        };
    }
}

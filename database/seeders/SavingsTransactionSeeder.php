<?php

namespace Database\Seeders;

use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Roles;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SavingsTransactionSeeder extends Seeder
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

        // Create savings types if not exist
        $savingsTypes = [
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Simpanan Pokok',
                'code' => 'SP',
                'amount' => 100000,
                'is_mandatory' => true,
                'description' => 'Simpanan pokok yang wajib dibayar saat menjadi anggota',
                'is_active' => true,
            ],
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Simpanan Wajib',
                'code' => 'SW',
                'amount' => 50000,
                'is_mandatory' => true,
                'description' => 'Simpanan wajib bulanan',
                'is_active' => true,
            ],
            [
                'cooperation_id' => $cooperation->id,
                'name' => 'Simpanan Sukarela',
                'code' => 'SS',
                'amount' => 0,
                'is_mandatory' => false,
                'description' => 'Simpanan sukarela sesuai kemampuan anggota',
                'is_active' => true,
            ],
        ];

        foreach ($savingsTypes as $type) {
            SavingsType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        // Get created savings types
        $savedSavingsTypes = SavingsType::whereIn('code', ['SP', 'SW', 'SS'])->get();

        // Create savings transactions for each anggota
        $adminUser = \App\Models\User::first(); // Get first user as admin
        if (!$adminUser) {
            $this->command->warn('No admin user found');
            return;
        }

        foreach ($anggotaUserIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $transactionNumber = 1;

            // Create Simpanan Pokok (one time)
            $simpananPokok = $savedSavingsTypes->where('code', 'SP')->first();
            if ($simpananPokok) {
                SavingsTransaction::create([
                    'cooperation_id' => $cooperation->id,
                    'user_id' => $userId,
                    'savings_type_id' => $simpananPokok->id,
                    'transaction_number' => 'SP-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($transactionNumber++, 3, '0', STR_PAD_LEFT),
                    'amount' => 100000,
                    'transaction_date' => Carbon::now()->subMonths(6),
                    'notes' => 'Simpanan pokok saat bergabung',
                    'receipt_number' => 'RCP-SP-' . time() . '-' . $userId,
                    'processed_by' => $adminUser->id,
                    'status' => 'completed',
                ]);
            }

            // Create Simpanan Wajib (monthly for last 6 months)
            $simpananWajib = $savedSavingsTypes->where('code', 'SW')->first();
            if ($simpananWajib) {
                for ($i = 6; $i >= 1; $i--) {
                    SavingsTransaction::create([
                        'cooperation_id' => $cooperation->id,
                        'user_id' => $userId,
                        'savings_type_id' => $simpananWajib->id,
                        'transaction_number' => 'SW-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($transactionNumber++, 3, '0', STR_PAD_LEFT),
                        'amount' => 50000,
                        'transaction_date' => Carbon::now()->subMonths($i),
                        'notes' => 'Simpanan wajib bulan ' . Carbon::now()->subMonths($i)->format('F Y'),
                        'receipt_number' => 'RCP-SW-' . time() . '-' . $userId . '-' . $i,
                        'processed_by' => $adminUser->id,
                        'status' => 'completed',
                    ]);
                }
            }

            // Create some Simpanan Sukarela (random amounts)
            $simpananSukarela = $savedSavingsTypes->where('code', 'SS')->first();
            if ($simpananSukarela) {
                $amounts = [25000, 50000, 75000, 100000, 150000];
                for ($i = 0; $i < 3; $i++) {
                    SavingsTransaction::create([
                        'cooperation_id' => $cooperation->id,
                        'user_id' => $userId,
                        'savings_type_id' => $simpananSukarela->id,
                        'transaction_number' => 'SS-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($transactionNumber++, 3, '0', STR_PAD_LEFT),
                        'amount' => $amounts[array_rand($amounts)],
                        'transaction_date' => Carbon::now()->subMonths(rand(1, 5)),
                        'notes' => 'Simpanan sukarela',
                        'receipt_number' => 'RCP-SS-' . time() . '-' . $userId . '-' . $i,
                        'processed_by' => $adminUser->id,
                        'status' => 'completed',
                    ]);
                }
            }
        }

        $this->command->info('Savings transactions seeded successfully');
    }
}

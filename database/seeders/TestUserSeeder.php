<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Cooperation;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $cooperation = Cooperation::first();
        $cooperationId = $cooperation ? $cooperation->id : 1;

        $users = [
            [
                'role' => 'admin',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'TADM001',
                    'name' => 'Test Admin',
                    'email' => 'admin@test.com',
                    'phone' => '0800000001',
                    'password' => bcrypt('password'),
                    'address' => 'Test',
                    'birth_date' => '1990-01-01',
                    'gender' => 'male',
                    'job' => 'Admin',
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'anggota',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'TANG001',
                    'name' => 'Test Anggota',
                    'email' => 'anggota@test.com',
                    'phone' => '0800000002',
                    'password' => bcrypt('password'),
                    'address' => 'Test',
                    'birth_date' => '1990-01-01',
                    'gender' => 'female',
                    'job' => 'Anggota',
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'kasir',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'TKSR001',
                    'name' => 'Test Kasir',
                    'email' => 'kasir@test.com',
                    'phone' => '0800000003',
                    'password' => bcrypt('password'),
                    'address' => 'Test',
                    'birth_date' => '1990-01-01',
                    'gender' => 'female',
                    'job' => 'Kasir',
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'spv',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'TSPV001',
                    'name' => 'Test SPV',
                    'email' => 'spv@test.com',
                    'phone' => '0800000004',
                    'password' => bcrypt('password'),
                    'address' => 'Test',
                    'birth_date' => '1990-01-01',
                    'gender' => 'male',
                    'job' => 'Supervisor',
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['data']['email']],
                $userData['data']
            );

            $role = Roles::where('name', $userData['role'])
                ->where('cooperation_id', $cooperationId)
                ->first();

            if ($role) {
                UserRole::where('user_id', $user->id)->delete();
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);
            }

            echo "✅ Test user: {$userData['data']['email']} ({$userData['role']})\n";
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Cooperation;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
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
                    'member_number' => 'ADM001',
                    'name' => 'Administrator',
                    'email' => 'admin@karya-tantri-abadi.test',
                    'phone' => '08123456789',
                    'password' => bcrypt('password'),
                    'address' => 'Jl. Administrasi No. 1',
                    'birth_date' => '1985-01-15',
                    'gender' => 'male',
                    'job' => 'Administrator',
                    'profile_photo' => null,
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'spv',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'SPV001',
                    'name' => 'Supervisor',
                    'email' => 'spv@karya-tantri-abadi.test',
                    'phone' => '08123456785',
                    'password' => bcrypt('password'),
                    'address' => 'Jl. Supervisor No. 5',
                    'birth_date' => '1982-12-05',
                    'gender' => 'male',
                    'job' => 'Supervisor',
                    'profile_photo' => null,
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'kasir',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'KSR001',
                    'name' => 'Kasir',
                    'email' => 'kasir@karya-tantri-abadi.test',
                    'phone' => '08123456787',
                    'password' => bcrypt('password'),
                    'address' => 'Jl. Kasir No. 3',
                    'birth_date' => '1992-06-10',
                    'gender' => 'female',
                    'job' => 'Kasir',
                    'profile_photo' => null,
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'anggota',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'ANG001',
                    'name' => 'Anggota',
                    'email' => 'anggota@karya-tantri-abadi.test',
                    'phone' => '08123456784',
                    'password' => bcrypt('password'),
                    'address' => 'Jl. Anggota No. 6',
                    'birth_date' => '1995-04-15',
                    'gender' => 'female',
                    'job' => 'Anggota',
                    'profile_photo' => null,
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'role' => 'anggota',
                'data' => [
                    'cooperation_id' => $cooperationId,
                    'member_number' => 'ANG002',
                    'name' => 'Anggota 2',
                    'email' => 'anggota2@karya-tantri-abadi.test',
                    'phone' => '08123456783',
                    'password' => bcrypt('password'),
                    'address' => 'Jl. Anggota No. 7',
                    'birth_date' => '1993-08-22',
                    'gender' => 'male',
                    'job' => 'Anggota',
                    'profile_photo' => null,
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            ],
        ];

        foreach ($users as $userData) {
            $existingUser = User::where('email', $userData['data']['email'])->first();

            if (!$existingUser) {
                $user = User::create($userData['data']);

                $role = Roles::where('name', $userData['role'])
                    ->where('cooperation_id', $cooperationId)
                    ->first();

                if ($role) {
                    UserRole::firstOrCreate([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                    ]);
                }

                echo "✅ Created user: {$userData['data']['name']} ({$userData['data']['email']}) with role: {$userData['role']}\n";
            } else {
                // re-sync role for existing users when re-seeding after role rename
                $role = Roles::where('name', $userData['role'])
                    ->where('cooperation_id', $cooperationId)
                    ->first();
                if ($role) {
                    UserRole::where('user_id', $existingUser->id)->delete();
                    UserRole::create([
                        'user_id' => $existingUser->id,
                        'role_id' => $role->id,
                    ]);
                    $existingUser->update([
                        'name' => $userData['data']['name'],
                        'is_active' => true,
                    ]);
                }
                echo "⚠️  Updated existing user: {$userData['data']['email']} -> {$userData['role']}\n";
            }
        }

        echo "\n📊 Roles: admin, spv, kasir, anggota\n";
        echo "🔑 Password semua: password\n";
        echo "📧 admin@karya-tantri-abadi.test | spv@karya-tantri-abadi.test | kasir@karya-tantri-abadi.test | anggota@karya-tantri-abadi.test\n";
    }
}

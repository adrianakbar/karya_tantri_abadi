<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasNasabahSeeder extends Seeder
{
    /**
     * Seed data dummy: 1 petugas + nasabah yang dia input (created_by = petugas).
     *
     * Petugas: petugas2@karya-tantri-abadi.test / password
     * Nasabah dibuat dengan created_by = id petugas, role anggota,
     * jadi bisa login di /anggota/login juga (anggota1..anggota5 / password).
     */
    public function run(): void
    {
        $cooperationId = \App\Models\Cooperation::first()?->id ?? 1;

        // ── 1. Petugas baru ──
        $petugas = User::firstOrCreate(
            ['email' => 'petugas2@karya-tantri-abadi.test'],
            [
                'cooperation_id' => $cooperationId,
                'member_number' => 'PTS002',
                'name' => 'Petugas Lapangan 2',
                'phone' => '08123456789',
                'password' => Hash::make('password'),
                'address' => 'Jl. Lapangan No. 12',
                'birth_date' => '1995-06-15',
                'gender' => 'male',
                'job' => 'Petugas',
                'join_date' => now(),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // attach role petugas
        $rolePetugas = Roles::where('name', 'petugas')->where('cooperation_id', $cooperationId)->first();
        if ($rolePetugas) {
            UserRole::firstOrCreate(['user_id' => $petugas->id, 'role_id' => $rolePetugas->id]);
        }

        // ── 2. Nasabah dummy yang diinput petugas ini ──
        $nasabahData = [
            ['member_number' => 'AGT003', 'name' => 'Budi Santoso',  'phone' => '08123456001', 'gender' => 'male',   'job' => 'Petani',      'address' => 'Ds. Sukorejo, Kec. Panti'],
            ['member_number' => 'AGT004', 'name' => 'Siti Aminah',   'phone' => '08123456002', 'gender' => 'female', 'job' => 'Pedagang',    'address' => 'Ds. Kemuning Lor, Kec. Panti'],
            ['member_number' => 'AGT005', 'name' => 'Ahmad Fauzi',   'phone' => '08123456003', 'gender' => 'male',   'job' => 'Buruh Tani',  'address' => 'Ds. Tanggul Wetan'],
            ['member_number' => 'AGT006', 'name' => 'Dewi Lestari',  'phone' => '08123456004', 'gender' => 'female', 'job' => 'Guru',        'address' => 'Ds. Sukorambi, Kec. Sukorambi'],
            ['member_number' => 'AGT007', 'name' => 'Joko Prasetyo', 'phone' => '08123456005', 'gender' => 'male',   'job' => 'Wiraswasta',  'address' => 'Ds. Jenggawah, Kec. Jenggawah'],
        ];

        $roleAnggota = Roles::where('name', 'anggota')->where('cooperation_id', $cooperationId)->first();

        foreach ($nasabahData as $i => $d) {
            $user = User::firstOrCreate(
                ['member_number' => $d['member_number']],
                [
                    'cooperation_id' => $cooperationId,
                    'name' => $d['name'],
                    'email' => strtolower(str_replace(" ", "", $d["member_number"])) . "@nasabah.test",
                    'phone' => $d['phone'],
                    'password' => Hash::make('password'),
                    'address' => $d['address'],
                    'birth_date' => '1990-0' . (($i % 9) + 1) . '-1' . $i,
                    'gender' => $d['gender'],
                    'job' => $d['job'],
                    'join_date' => now(),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'created_by' => $petugas->id,
                ]
            );

            if ($roleAnggota) {
                UserRole::firstOrCreate(['user_id' => $user->id, 'role_id' => $roleAnggota->id]);
            }
        }

        $this->command->info('✅ PetugasNasabahSeeder selesai: petugas2 + 5 nasabah (created_by = petugas2)');
    }
}

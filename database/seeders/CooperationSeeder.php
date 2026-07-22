<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CooperationSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Cooperation::firstOrCreate(
            ['code' => 'KTA'],
            [
                'name' => 'Karya Tantri Abadi',
                'address' => 'Indonesia',
                'phone' => '08123456789',
                'email' => 'info@karya-tantri-abadi.test',
                'logo_url' => null,
                'is_active' => true,
            ]
        );
    }
}

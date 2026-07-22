<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Option 1: Use CompleteSystemSeeder (recommended for fresh install)
        // $this->call([CompleteSystemSeeder::class]);
        
        // Option 2: Use individual seeders (for selective seeding)
        $this->call([
            CooperationSeeder::class,
            PermissionAndRoleSeeder::class,
            UserSeeder::class,
            TestUserSeeder::class,
            SystemSettingsSeeder::class,
            // Modul barang/inventaris dinonaktifkan untuk Karya Tantri Abadi
            // ProductCategorySeeder::class,
            // ProductSeeder::class,
            SavingsTransactionSeeder::class,
            LoanSeeder::class,
        ]);
        
        // To use CompleteSystemSeeder, uncomment line above and comment out individual seeders
    }
}

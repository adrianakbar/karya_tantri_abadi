<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cooperation = \App\Models\Cooperation::first();
        
        if (!$cooperation) {
            throw new \Exception('No cooperation found. Please run CooperationSeeder first.');
        }

        $settings = [
            // General Settings
            [
                'category' => 'general',
                'key' => 'app_name',
                'value' => 'Karya Tantri Abadi',
                'type' => 'string',
                'description' => 'Nama aplikasi yang ditampilkan',
                'is_system' => true,
            ],
            [
                'category' => 'general',
                'key' => 'cooperation_name',
                'value' => $cooperation->name,
                'type' => 'string',
                'description' => 'Nama organisasi',
                'is_system' => false,
            ],
            [
                'category' => 'general',
                'key' => 'cooperation_address',
                'value' => $cooperation->address,
                'type' => 'string',
                'description' => 'Alamat organisasi',
                'is_system' => false,
            ],

            // UI Theme Settings
            [
                'category' => 'ui_theme',
                'key' => 'primary_color',
                'value' => '#1f2937',
                'type' => 'string',
                'description' => 'Warna utama tema',
                'is_system' => false,
            ],
            [
                'category' => 'ui_theme',
                'key' => 'secondary_color',
                'value' => '#4f46e5',
                'type' => 'string',
                'description' => 'Warna sekunder tema',
                'is_system' => false,
            ],
            [
                'category' => 'ui_theme',
                'key' => 'dark_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Mode gelap',
                'is_system' => false,
            ],

            // Notification Settings
            [
                'category' => 'notification',
                'key' => 'low_stock_threshold',
                'value' => '10',
                'type' => 'number',
                'description' => 'Batas minimal stok untuk notifikasi',
                'is_system' => false,
            ],
            [
                'category' => 'notification',
                'key' => 'enable_email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi email',
                'is_system' => false,
            ],

            // Report Schedule Settings
            [
                'category' => 'report_schedule',
                'key' => 'monthly_report_day',
                'value' => '1',
                'type' => 'number',
                'description' => 'Tanggal pembuatan laporan bulanan',
                'is_system' => false,
            ],
            [
                'category' => 'report_schedule',
                'key' => 'daily_backup_time',
                'value' => '23:00',
                'type' => 'string',
                'description' => 'Waktu backup harian',
                'is_system' => false,
            ],

            // Financial Settings
            [
                'category' => 'financial',
                'key' => 'currency',
                'value' => 'IDR',
                'type' => 'string',
                'description' => 'Mata uang',
                'is_system' => true,
            ],
            [
                'category' => 'financial',
                'key' => 'decimal_separator',
                'value' => ',',
                'type' => 'string',
                'description' => 'Pemisah desimal',
                'is_system' => false,
            ],
            [
                'category' => 'financial',
                'key' => 'thousand_separator',
                'value' => '.',
                'type' => 'string',
                'description' => 'Pemisah ribuan',
                'is_system' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                [
                    'key' => $setting['key'],
                    'cooperation_id' => $cooperation->id,
                ],
                [
                    'category' => $setting['category'],
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                    'is_system' => $setting['is_system'],
                ]
            );
        }
    }
}

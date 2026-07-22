<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\Cooperation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        return [
            'cooperation_id' => Cooperation::factory(),
            'category' => fake()->randomElement(['general', 'ui_theme', 'notification', 'backup', 'report_schedule', 'financial', 'inventory']),
            'key' => fake()->unique()->word(),
            'value' => json_encode(['setting_val' => fake()->word()]),
            'type' => 'json',
            'description' => fake()->sentence(),
            'is_system' => false,
            'updated_by' => User::factory(),
        ];
    }
}

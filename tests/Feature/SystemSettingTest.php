<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\SystemSetting;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $cooperation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cooperation = Cooperation::factory()->create();

        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);

        $adminRole = Roles::firstOrCreate([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'admin',
        ]);
        UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id,
        ]);

        // Seed permissions for setting management
        $viewPermission = \App\Models\Permissions::firstOrCreate([
            'name' => 'view_settings',
            'module' => 'settings',
        ]);
        $managePermission = \App\Models\Permissions::firstOrCreate([
            'name' => 'manage_settings',
            'module' => 'settings',
        ]);

        \App\Models\RolePermissions::firstOrCreate([
            'role_id' => $adminRole->id,
            'permission_id' => $viewPermission->id,
        ]);
        \App\Models\RolePermissions::firstOrCreate([
            'role_id' => $adminRole->id,
            'permission_id' => $managePermission->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function user_can_list_system_settings()
    {
        $setting = SystemSetting::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'category' => 'general',
            'key' => 'cooperative_name',
            'value' => 'Koperasi Maju Bersama',
            'type' => 'string',
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableRecords([$setting]);
    }

    /** @test */
    public function user_can_create_system_setting()
    {
        Livewire::test(\App\Filament\Resources\SystemSettingResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'category' => 'ui_theme',
                'key' => 'theme_color',
                'type' => 'string',
                'value' => 'green',
                'description' => 'Primary theme color',
                'is_system' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_settings', [
            'cooperation_id' => $this->cooperation->id,
            'category' => 'ui_theme',
            'value' => json_encode('green'),
        ]);
    }
}

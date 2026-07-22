<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\AuthLogResource;
use App\Filament\Resources\DataChangeLogResource;
use App\Filament\Pages\AuditTrailPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class AuditTrailFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cooperation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cooperation = Cooperation::create([
            'code' => 'TEST001',
            'name' => 'Test Cooperation',
            'address' => 'Test Address',
            'phone' => '08123456789',
            'email' => 'test@cooperation.com',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);

        // Create and assign admin role for authorization
        $adminRole = \App\Models\Roles::create([
            'name' => 'admin',
            'cooperation_id' => $this->cooperation->id
        ]);
        \App\Models\UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id
        ]);
    }

    /** @test */
    public function activity_log_resource_can_list_records()
    {
        // Create test activity logs
        ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard Access',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'create',
            'module' => 'products',
            'description' => 'Create Product',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ActivityLogResource\Pages\ListActivityLogs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(ActivityLog::forCooperation($this->cooperation->id)->get());
    }

    /** @test */
    public function activity_log_resource_can_view_record()
    {
        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard Access',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->actingAs($this->user);
        
        Livewire::test(ActivityLogResource\Pages\ViewActivityLog::class, ['record' => $log->id])
            ->assertSuccessful()
            ->assertFormSet([
                'user_id' => $this->user->id,
                'action' => 'view',
                'module' => 'dashboard',
                'description' => 'Dashboard Access',
                'ip_address' => '127.0.0.1',
            ]);
    }

    /** @test */
    public function auth_log_resource_can_list_records()
    {
        // Create test auth logs
        AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'logout',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->actingAs($this->user);

        Livewire::test(AuthLogResource\Pages\ListAuthLogs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(AuthLog::forCooperation($this->cooperation->id)->get());
    }

    /** @test */
    public function auth_log_resource_can_view_record()
    {
        $log = AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->actingAs($this->user);

        Livewire::test(AuthLogResource\Pages\ViewAuthLog::class, ['record' => $log->id])
            ->assertSuccessful()
            ->assertFormSet([
                'user_id' => $this->user->id,
                'action' => 'login',
                'ip_address' => '127.0.0.1',
            ]);
    }

    /** @test */
    public function data_change_log_resource_can_list_records()
    {
        // Create test data change logs
        DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'create',
            'old_values' => null,
            'new_values' => json_encode(['name' => 'Test User']),
            'changed_at' => now(),
        ]);

        DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'products',
            'record_id' => 1,
            'action' => 'update',
            'old_values' => json_encode(['name' => 'Old Product']),
            'new_values' => json_encode(['name' => 'New Product']),
            'changed_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(DataChangeLogResource\Pages\ListDataChangeLogs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(DataChangeLog::forCooperation($this->cooperation->id)->get());
    }

    /** @test */
    public function data_change_log_resource_can_view_record()
    {
        $log = DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'update',
            'old_values' => json_encode(['name' => 'Old Name']),
            'new_values' => json_encode(['name' => 'New Name']),
            'changed_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(DataChangeLogResource\Pages\ViewDataChangeLog::class, ['record' => $log->id])
            ->assertSuccessful()
            ->assertFormSet([
                'user_id' => $this->user->id,
                'table_name' => 'users',
                'record_id' => 1,
                'action' => 'update',
            ]);
    }

    /** @test */
    public function audit_trail_page_loads_successfully()
    {
        // Create some test data
        ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard Access',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'create',
            'old_values' => null,
            'new_values' => json_encode(['name' => 'Test']),
            'changed_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(AuditTrailPage::class)
            ->assertSuccessful()
            ->assertSee('Log Aktivitas (Audit Trail)')
            ->assertSee('Riwayat Login')
            ->assertSee('Perubahan Data');
    }

    /** @test */
    public function activity_log_resource_filters_by_cooperation()
    {
        // Create another cooperation and user
        $otherCooperation = Cooperation::create([
            'code' => 'OTHER001',
            'name' => 'Other Cooperation',
            'address' => 'Other Address',
            'phone' => '08987654321',
            'email' => 'other@cooperation.com',
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'cooperation_id' => $otherCooperation->id,
        ]);

        // Create logs for both cooperations
        ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Test Cooperation Log',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        ActivityLog::create([
            'user_id' => $otherUser->id,
            'cooperation_id' => $otherCooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Other Cooperation Log',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        // Test that user can only see their cooperation's logs
        $this->actingAs($this->user);

        Livewire::test(ActivityLogResource\Pages\ListActivityLogs::class)
            ->assertSuccessful()
            ->assertSee('Test Cooperation Log')
            ->assertDontSee('Other Cooperation Log');

        // Test with other user
        $this->actingAs($otherUser);

        Livewire::test(ActivityLogResource\Pages\ListActivityLogs::class)
            ->assertSuccessful()
            ->assertSee('Other Cooperation Log')
            ->assertDontSee('Test Cooperation Log');
    }

    /** @test */
    public function resource_navigation_works_correctly()
    {
        $this->actingAs($this->user);

        // Test that resources are properly grouped
        $this->assertEquals('Log Aktivitas', ActivityLogResource::getNavigationGroup());
        $this->assertEquals('Log Aktivitas', AuthLogResource::getNavigationGroup());
        $this->assertEquals('Log Aktivitas', DataChangeLogResource::getNavigationGroup());

        // Test navigation labels
        $this->assertEquals('Semua Aktivitas', ActivityLogResource::getNavigationLabel());
        $this->assertEquals('Riwayat Login', AuthLogResource::getNavigationLabel());
        $this->assertEquals('Perubahan Data', DataChangeLogResource::getNavigationLabel());

        // Test model labels
        $this->assertEquals('Log Aktivitas', ActivityLogResource::getModelLabel());
        $this->assertEquals('Riwayat Login', AuthLogResource::getModelLabel());
        $this->assertEquals('Riwayat Perubahan Data', DataChangeLogResource::getModelLabel());
    }
}

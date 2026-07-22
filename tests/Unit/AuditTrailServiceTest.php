<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use App\Services\AuditTrailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditTrailServiceTest extends TestCase
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
    }

    /** @test */
    public function log_auth_creates_auth_log_record()
    {
        $request = \Illuminate\Http\Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logAuth('login', $this->user->id, $this->cooperation->id);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);
    }

    /** @test */
    public function log_auth_handles_null_user()
    {
        $request = \Illuminate\Http\Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logAuth('failed_login', null, null);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => null,
            'cooperation_id' => null,
            'action' => 'failed_login',
        ]);
    }

    /** @test */
    public function log_activity_creates_activity_log_record()
    {
        $request = \Illuminate\Http\Request::create('/admin', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logActivity(
            'view',
            'dashboard',
            'Dashboard Access',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard Access',
        ]);
    }

    /** @test */
    public function log_activity_handles_null_user()
    {
        $request = \Illuminate\Http\Request::create('/admin', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'System Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logActivity(
            'system',
            'maintenance',
            'System Maintenance',
            null,
            null
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'cooperation_id' => null,
            'action' => 'system',
            'module' => 'maintenance',
        ]);
    }

    /** @test */
    public function log_data_change_creates_data_change_log_record()
    {
        $oldData = ['name' => 'Old Name'];
        $newData = ['name' => 'New Name'];

        AuditTrailService::logDataChange(
            $this->user,
            'update',
            $oldData,
            $newData,
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('data_change_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => $this->user->id,
            'action' => 'update',
        ]);
    }

    /** @test */
    public function log_data_change_handles_create_action()
    {
        $newData = ['name' => 'New Record', 'email' => 'new@example.com'];

        AuditTrailService::logDataChange(
            $this->user,
            'create',
            null,
            $newData,
            $this->user->id,
            $this->cooperation->id
        );
        
        $log = DataChangeLog::where('table_name', 'users')
            ->where('record_id', $this->user->id)
            ->where('action', 'create')
            ->first();

        $this->assertNotNull($log);
        $this->assertNull($log->old_values);
        $this->assertEquals($newData, $log->new_values);
    }

    /** @test */
    public function log_data_change_handles_delete_action()
    {
        $oldData = ['name' => 'Deleted Record', 'email' => 'deleted@example.com'];

        AuditTrailService::logDataChange(
            $this->user,
            'delete',
            $oldData,
            null,
            $this->user->id,
            $this->cooperation->id
        );
        
        $log = DataChangeLog::where('table_name', 'users')
            ->where('record_id', $this->user->id)
            ->where('action', 'delete')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($oldData, $log->old_values);
        $this->assertNull($log->new_values);
    }

    /** @test */
    public function log_report_generation_creates_activity_log()
    {
        Auth::login($this->user);

        AuditTrailService::logReportGeneration(
            'sales_report',
            ['month' => '2025-09', 'category' => 'electronics']
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'generate_report',
            'module' => 'reports',
            'description' => 'Generated sales_report report with parameters: {"month":"2025-09","category":"electronics"}',
        ]);
    }

    /** @test */
    public function service_respects_configuration_settings()
    {
        // Test with audit disabled
        config(['audit.enabled' => false]);
        
        AuditTrailService::logActivity(
            'view',
            'test',
            'Test Description',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'Test Description',
        ]);

        // Test with audit enabled
        config(['audit.enabled' => true]);
        
        AuditTrailService::logActivity(
            'view',
            'test2',
            'Test Description 2',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Test Description 2',
        ]);
    }

    /** @test */
    public function service_handles_large_data_changes()
    {
        $largeOldData = array_fill(0, 100, ['field' => 'old_value']);
        $largeNewData = array_fill(0, 100, ['field' => 'new_value']);

        AuditTrailService::logDataChange(
            $this->user,
            'update',
            $largeOldData,
            $largeNewData,
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('data_change_logs', [
            'table_name' => 'users',
            'record_id' => $this->user->id,
            'action' => 'update',
        ]);
    }
}

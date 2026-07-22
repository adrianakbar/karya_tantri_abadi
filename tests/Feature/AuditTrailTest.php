<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use App\Services\AuditTrailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $cooperation;
    protected $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test cooperation
        $this->cooperation = Cooperation::create([
            'code' => 'TEST001',
            'name' => 'Test Cooperation',
            'address' => 'Test Address',
            'phone' => '08123456789',
            'email' => 'test@cooperation.com',
        ]);

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);

        $this->auditService = new AuditTrailService();
    }

    /** @test */
    public function it_can_log_user_authentication()
    {
        // Test login logging
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

        // Test logout logging
        $request = \Illuminate\Http\Request::create('/admin/logout', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logAuth('logout', $this->user->id, $this->cooperation->id);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'logout',
        ]);

        // Test failed login logging
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
    public function it_can_log_user_activity()
    {
        Auth::login($this->user);

        $request = \Illuminate\Http\Request::create('/admin', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);
        app()->instance('request', $request);

        AuditTrailService::logActivity(
            'view',
            'dashboard',
            'Dashboard',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard',
            'ip_address' => '127.0.0.1',
        ]);
    }

    /** @test */
    public function it_can_log_data_changes()
    {
        Auth::login($this->user);

        $oldData = ['name' => 'Old Name', 'email' => 'old@example.com'];
        $newData = ['name' => 'New Name', 'email' => 'new@example.com'];

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

        $log = DataChangeLog::where('table_name', 'users')->first();
        $this->assertNotNull($log);
        $this->assertEquals($oldData, $log->old_values);
        $this->assertEquals($newData, $log->new_values);
    }

    /** @test */
    public function it_can_log_report_generation()
    {
        Auth::login($this->user);

        AuditTrailService::logReportGeneration(
            'sales_report',
            ['month' => '2025-09']
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'generate_report',
            'module' => 'reports',
            'description' => 'Generated sales_report report with parameters: {"month":"2025-09"}',
        ]);
    }

    /** @test */
    public function activity_log_has_correct_formatted_action()
    {
        $log = ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'Dashboard',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertEquals('View', $log->formatted_action);
    }

    /** @test */
    public function auth_log_has_correct_formatted_action()
    {
        $loginLog = AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertEquals('Login', $loginLog->formatted_action);

        $logoutLog = AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'logout',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertEquals('Logout', $logoutLog->formatted_action);

        $failedLog = AuthLog::create([
            'cooperation_id' => null,
            'action' => 'failed_login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Failed Login', $failedLog->formatted_action);
    }

    /** @test */
    public function data_change_log_has_correct_formatted_action()
    {
        $createLog = DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'create',
            'old_values' => null,
            'new_values' => json_encode(['name' => 'Test']),
            'changed_at' => now(),
        ]);

        $this->assertEquals('Created', $createLog->formatted_action);

        $updateLog = DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'update',
            'old_values' => json_encode(['name' => 'Old']),
            'new_values' => json_encode(['name' => 'New']),
            'changed_at' => now(),
        ]);

        $this->assertEquals('Updated', $updateLog->formatted_action);

        $deleteLog = DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => 1,
            'action' => 'delete',
            'old_values' => json_encode(['name' => 'Test']),
            'new_values' => null,
            'changed_at' => now(),
        ]);

        $this->assertEquals('Deleted', $deleteLog->formatted_action);
    }

    /** @test */
    public function it_filters_logs_by_cooperation()
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

        // Test cooperation filtering
        $testCooperationLogs = ActivityLog::forCooperation($this->cooperation->id)->get();
        $otherCooperationLogs = ActivityLog::forCooperation($otherCooperation->id)->get();

        $this->assertCount(1, $testCooperationLogs);
        $this->assertCount(1, $otherCooperationLogs);
        $this->assertEquals('Test Cooperation Log', $testCooperationLogs->first()->description);
        $this->assertEquals('Other Cooperation Log', $otherCooperationLogs->first()->description);
    }

    /** @test */
    public function it_handles_configuration_settings()
    {
        // Test with audit disabled
        config(['audit.enabled' => false]);
        
        $initialCount = ActivityLog::count();
        
        AuditTrailService::logActivity(
            'view',
            'dashboard',
            'Dashboard',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertEquals($initialCount, ActivityLog::count());

        // Test with audit enabled
        config(['audit.enabled' => true]);
        
        AuditTrailService::logActivity(
            'view',
            'dashboard',
            'Dashboard',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertEquals($initialCount + 1, ActivityLog::count());
    }
}

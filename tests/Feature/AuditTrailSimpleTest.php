<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use App\Services\AuditTrailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected $cooperation;
    protected $user;
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
    public function it_can_log_authentication_events()
    {
        // Test login logging
        AuditTrailService::logAuth('login', $this->user->id, $this->cooperation->id);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
        ]);

        // Test logout logging
        AuditTrailService::logAuth('logout', $this->user->id, $this->cooperation->id);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'logout',
        ]);
    }

    /** @test */
    public function it_can_log_user_activities()
    {
        AuditTrailService::logActivity(
            'view',
            'dashboard',
            'User viewed dashboard',
            $this->user->id,
            $this->cooperation->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'view',
            'module' => 'dashboard',
            'description' => 'User viewed dashboard',
        ]);
    }

    /** @test */
    public function it_can_log_data_changes()
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
    public function it_filters_by_cooperation()
    {
        // Create another cooperation and user
        $cooperation2 = Cooperation::create([
            'code' => 'TEST002',
            'name' => 'Test Cooperation 2',
            'address' => 'Test Address 2',
            'phone' => '08123456780',
            'email' => 'test2@cooperation.com',
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'cooperation_id' => $cooperation2->id,
        ]);

        // Log activities for both cooperations
        AuditTrailService::logActivity('view', 'dashboard', 'Test 1', $this->user->id, $this->cooperation->id);
        AuditTrailService::logActivity('view', 'dashboard', 'Test 2', $user2->id, $cooperation2->id);

        // Check that each cooperation only sees its own logs
        $logs1 = ActivityLog::where('cooperation_id', $this->cooperation->id)->get();
        $logs2 = ActivityLog::where('cooperation_id', $cooperation2->id)->get();

        $this->assertCount(1, $logs1);
        $this->assertCount(1, $logs2);
        $this->assertEquals('Test 1', $logs1->first()->description);
        $this->assertEquals('Test 2', $logs2->first()->description);
    }

    /** @test */
    public function it_handles_guest_user_logging()
    {
        // Test logging without user (guest activity)
        AuditTrailService::logActivity(
            'view',
            'homepage',
            'Guest viewed homepage',
            null,
            null
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => null,
            'cooperation_id' => null,
            'action' => 'view',
            'module' => 'homepage',
            'description' => 'Guest viewed homepage',
        ]);
    }

    /** @test */
    public function models_are_properly_configured()
    {
        // Test ActivityLog model
        $activityLog = ActivityLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'test',
            'module' => 'test_module',
            'description' => 'Test description',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertInstanceOf(ActivityLog::class, $activityLog);
        $this->assertEquals('test', $activityLog->action);

        // Test AuthLog model
        $authLog = AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->assertInstanceOf(AuthLog::class, $authLog);
        $this->assertEquals('login', $authLog->action);

        // Test DataChangeLog model
        $dataChangeLog = DataChangeLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'table_name' => 'users',
            'record_id' => $this->user->id,
            'action' => 'update',
            'old_values' => json_encode(['name' => 'Old']),
            'new_values' => json_encode(['name' => 'New']),
        ]);

        $this->assertInstanceOf(DataChangeLog::class, $dataChangeLog);
        $this->assertEquals('update', $dataChangeLog->action);
    }
}

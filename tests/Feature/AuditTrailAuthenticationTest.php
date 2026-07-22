<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\AuthLog;
use App\Listeners\AuthenticationListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditTrailAuthenticationTest extends TestCase
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

        $adminRole = \App\Models\Roles::firstOrCreate([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'admin',
        ]);
        \App\Models\UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id,
        ]);
    }

    /** @test */
    public function it_logs_successful_login()
    {
        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test User Agent',
        ]);

        app()->instance('request', $request);

        $listener = new AuthenticationListener();
        $event = new Login('web', $this->user, false);
        
        $listener->handleLogin($event);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);
    }

    /** @test */
    public function it_logs_logout()
    {
        $request = Request::create('/admin/logout', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_USER_AGENT' => 'Another User Agent',
        ]);

        app()->instance('request', $request);

        $listener = new AuthenticationListener();
        $event = new Logout('web', $this->user);
        
        $listener->handleLogout($event);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'logout',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Another User Agent',
        ]);
    }

    /** @test */
    public function it_logs_failed_login_with_email()
    {
        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Failed Login Agent',
        ]);
        $request->merge(['email' => 'wrong@example.com']);

        app()->instance('request', $request);

        $listener = new AuthenticationListener();
        $event = new Failed('web', null, ['email' => 'wrong@example.com', 'password' => 'wrong']);
        
        $listener->handleFailed($event);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => null,
            'cooperation_id' => null,
            'action' => 'failed_login',
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Failed Login Agent',
        ]);
    }

    /** @test */
    public function it_logs_failed_login_without_email()
    {
        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '172.16.0.1',
            'HTTP_USER_AGENT' => 'No Email Agent',
        ]);

        app()->instance('request', $request);

        $listener = new AuthenticationListener();
        $event = new Failed('web', null, ['password' => 'wrong']);
        
        $listener->handleFailed($event);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => null,
            'cooperation_id' => null,
            'action' => 'failed_login',
            'ip_address' => '172.16.0.1',
            'user_agent' => 'No Email Agent',
        ]);
    }

    /** @test */
    public function it_handles_missing_request_data()
    {
        // Test with minimal request data
        $request = Request::create('/admin/login', 'POST');

        app()->instance('request', $request);

        $listener = new AuthenticationListener();
        $event = new Login('web', $this->user, false);
        
        $listener->handleLogin($event);

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
        ]);

        $log = AuthLog::where('user_id', $this->user->id)
            ->where('action', 'login')
            ->first();

        // Should handle missing IP and user agent gracefully
        $this->assertNotNull($log);
    }

    /** @test */
    public function authentication_events_are_registered()
    {
        // Test that we can trigger actual authentication events
        $initialCount = AuthLog::count();

        // Simulate login
        $this->post('/admin/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        // Should have logged the login
        $this->assertGreaterThan($initialCount, AuthLog::count());

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'action' => 'login',
        ]);
    }

    /** @test */
    public function failed_authentication_event_is_logged()
    {
        $initialCount = AuthLog::count();

        // Simulate failed login
        $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        // Should have logged the failed login
        $this->assertGreaterThan($initialCount, AuthLog::count());

        $this->assertDatabaseHas('auth_logs', [
            'action' => 'failed_login',
        ]);
    }

    /** @test */
    public function logout_event_is_logged()
    {
        // Login first
        $this->actingAs($this->user);

        $initialCount = AuthLog::count();

        // Simulate logout
        $this->post('/admin/logout');

        // Should have logged the logout
        $this->assertGreaterThan($initialCount, AuthLog::count());

        $this->assertDatabaseHas('auth_logs', [
            'user_id' => $this->user->id,
            'action' => 'logout',
        ]);
    }

    /** @test */
    public function auth_logs_are_filtered_by_cooperation()
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

        // Create auth logs for both users
        AuthLog::create([
            'user_id' => $this->user->id,
            'cooperation_id' => $this->cooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        AuthLog::create([
            'user_id' => $otherUser->id,
            'cooperation_id' => $otherCooperation->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        // Test cooperation filtering
        $testCooperationLogs = AuthLog::forCooperation($this->cooperation->id)->get();
        $otherCooperationLogs = AuthLog::forCooperation($otherCooperation->id)->get();

        $this->assertCount(1, $testCooperationLogs);
        $this->assertCount(1, $otherCooperationLogs);
        $this->assertEquals($this->user->id, $testCooperationLogs->first()->user_id);
        $this->assertEquals($otherUser->id, $otherCooperationLogs->first()->user_id);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $cooperation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock cooperation
        $this->cooperation = Cooperation::create([
            'name' => 'Koperasi Test',
            'code' => 'KOP-TEST',
            'phone' => '08123456789',
            'email' => 'kop@test.com',
            'address' => 'Test Address',
        ]);

        // Create a user
        $this->user = User::create([
            'cooperation_id' => $this->cooperation->id,
            'member_number' => 'MEM-001',
            'name' => 'Test Member',
            'email' => 'member@test.com',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'join_date' => now(),
        ]);
    }

    /**
     * Test successful login.
     */
    public function test_user_can_login_with_email()
    {
        $response = $this->postJson('/api/login', [
            'login' => 'member@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user'
                ]
            ]);
    }

    /**
     * Test login with member number.
     */
    public function test_user_can_login_with_member_number()
    {
        $response = $this->postJson('/api/login', [
            'login' => 'MEM-001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['token']
            ]);
    }

    /**
     * Test login failure.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'login' => 'member@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Kredensial login salah.'
            ]);
    }

    /**
     * Test accessing protected endpoints without token.
     */
    public function test_cannot_access_profile_without_token()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /**
     * Test access with authenticated token.
     */
    public function test_authenticated_user_can_access_endpoints()
    {
        $token = $this->user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'member@test.com');
    }

    /**
     * Test dashboard endpoint.
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $token = $this->user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_savings',
                    'total_remaining_loan',
                    'total_purchases',
                    'recent_savings',
                    'recent_purchases',
                ]
            ]);
    }
}

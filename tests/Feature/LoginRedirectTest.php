<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected $adminRole;
    protected $anggotaRole;
    protected $bendaharaRole;
    protected $kepalaYayasanRole;
    protected $kepalaYayasanRole2;
    
    protected $adminUser;
    protected $anggotaUser;
    protected $bendaharaUser;
    protected $kepalaYayasanUser;
    protected $cooperation;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create cooperation first
        $this->cooperation = \App\Models\Cooperation::create([
            'code' => 'TEST001',
            'name' => 'Test Cooperation',
            'address' => 'Test Address',
            'phone' => '08123456789',
            'email' => 'test@cooperation.com',
        ]);
        
        // Create roles with cooperation_id
        $this->adminRole = Roles::create(['name' => 'admin', 'cooperation_id' => $this->cooperation->id]);
        $this->anggotaRole = Roles::create(['name' => 'anggota', 'cooperation_id' => $this->cooperation->id]);
        $this->bendaharaRole = Roles::create(['name' => 'bendahara', 'cooperation_id' => $this->cooperation->id]);
        $this->kepalaYayasanRole = Roles::create(['name' => 'kepala_yayasan', 'cooperation_id' => $this->cooperation->id]);
        $this->kepalaYayasanRole2 = Roles::create(['name' => 'kepalayayasan', 'cooperation_id' => $this->cooperation->id]);
        
        // Create test users
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);
        
        $this->anggotaUser = User::create([
            'name' => 'Anggota User',
            'email' => 'anggota@test.com',
            'password' => Hash::make('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);
        
        $this->bendaharaUser = User::create([
            'name' => 'Bendahara User',
            'email' => 'bendahara@test.com',
            'password' => Hash::make('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);
        
        $this->kepalaYayasanUser = User::create([
            'name' => 'Kepala Yayasan User',
            'email' => 'kepalayayasan@test.com',
            'password' => Hash::make('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);
        
        // Assign roles to users
        UserRole::create(['user_id' => $this->adminUser->id, 'role_id' => $this->adminRole->id]);
        UserRole::create(['user_id' => $this->anggotaUser->id, 'role_id' => $this->anggotaRole->id]);
        UserRole::create(['user_id' => $this->bendaharaUser->id, 'role_id' => $this->bendaharaRole->id]);
        UserRole::create(['user_id' => $this->kepalaYayasanUser->id, 'role_id' => $this->kepalaYayasanRole2->id]);
    }

    /** @test */
    public function admin_user_can_login_and_stays_in_admin_panel()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function anggota_user_redirects_to_anggota_panel_after_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'anggota@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/anggota/simpanan');
    }

    /** @test */
    public function bendahara_user_redirects_to_bendahara_panel_after_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'bendahara@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/bendahara');
    }

    /** @test */
    public function kepala_yayasan_user_redirects_to_kepala_yayasan_panel_after_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'kepalayayasan@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/kepalayayasan/financial-report');
    }

    /** @test */
    public function user_without_role_redirects_to_default_anggota_panel()
    {
        $userWithoutRole = User::create([
            'name' => 'User Without Role',
            'email' => 'norole@test.com',
            'password' => Hash::make('password'),
            'cooperation_id' => $this->cooperation->id,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'norole@test.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/anggota/simpanan');
    }

    /** @test */
    public function root_path_redirects_unauthenticated_users_to_admin_login()
    {
        $response = $this->get('/');
        
        $response->assertRedirect('/auth/login');
    }

    /** @test */
    public function login_path_redirects_to_admin_login()
    {
        $response = $this->get('/login');
        
        $response->assertRedirect('/auth/login');
    }

    /** @test */
    public function authenticated_admin_accessing_root_redirects_to_admin_panel()
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get('/');
        
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function authenticated_anggota_accessing_root_redirects_to_anggota_panel()
    {
        $this->actingAs($this->anggotaUser);
        
        $response = $this->get('/');
        
        $response->assertRedirect('/anggota/simpanan');
    }

    /** @test */
    public function non_admin_user_accessing_admin_dashboard_gets_redirected()
    {
        // Test with anggota user
        $response = $this->actingAs($this->anggotaUser)
                        ->get('/admin');

        $response->assertRedirect('/anggota/simpanan');
    }

    /** @test */
    public function admin_user_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
                        ->get('/admin');

        $response->assertOk();
    }

    /** @test */
    public function invalid_credentials_returns_error()
    {
        $response = $this->post('/admin/login', [
            'email' => 'invalid@test.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function custom_login_response_is_bound()
    {
        $binding = app('Filament\Http\Responses\Auth\Contracts\LoginResponse');
        
        $this->assertInstanceOf(\App\Http\Responses\CustomLoginResponse::class, $binding);
    }

    /** @test */
    public function redirect_based_on_role_middleware_works()
    {
        // Login as anggota user first
        $this->post('/admin/login', [
            'email' => 'anggota@test.com',
            'password' => 'password',
        ]);

        // Try to access admin dashboard
        $response = $this->get('/admin');
        
        // Should be redirected to anggota panel
        $response->assertRedirect('/anggota/simpanan');
    }
}
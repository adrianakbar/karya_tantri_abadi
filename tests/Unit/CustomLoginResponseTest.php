<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Responses\CustomLoginResponse;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomLoginResponseTest extends TestCase
{
    use RefreshDatabase;

    protected $customLoginResponse;
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customLoginResponse = new CustomLoginResponse();
        $this->request = Request::create('/admin/login', 'POST');
    }

    /** @test */
    public function returns_redirect_response_for_admin_user()
    {
        // Create admin role and user
        $adminRole = Roles::create(['name' => 'admin', 'cooperation_id' => 1]);
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $adminUser->id, 'role_id' => $adminRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($adminUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/admin', $response->getTargetUrl());
    }

    /** @test */
    public function returns_redirect_response_for_anggota_user()
    {
        // Create anggota role and user
        $anggotaRole = Roles::create(['name' => 'anggota']);
        $anggotaUser = User::create([
            'name' => 'Anggota User',
            'email' => 'anggota@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $anggotaUser->id, 'role_id' => $anggotaRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($anggotaUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/anggota/simpanan', $response->getTargetUrl());
    }

    /** @test */
    public function returns_redirect_response_for_bendahara_user()
    {
        // Create bendahara role and user
        $bendaharaRole = Roles::create(['name' => 'bendahara']);
        $bendaharaUser = User::create([
            'name' => 'Bendahara User',
            'email' => 'bendahara@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $bendaharaUser->id, 'role_id' => $bendaharaRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($bendaharaUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/bendahara', $response->getTargetUrl());
    }

    /** @test */
    public function returns_redirect_response_for_kepala_yayasan_user()
    {
        // Create kepala yayasan role and user
        $kepalaYayasanRole = Roles::create(['name' => 'kepalayayasan']);
        $kepalaYayasanUser = User::create([
            'name' => 'Kepala Yayasan User',
            'email' => 'kepalayayasan@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $kepalaYayasanUser->id, 'role_id' => $kepalaYayasanRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($kepalaYayasanUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/kepalayayasan/financial-report', $response->getTargetUrl());
    }

    /** @test */
    public function returns_default_redirect_for_user_without_role()
    {
        // Create user without role
        $userWithoutRole = User::create([
            'name' => 'User Without Role',
            'email' => 'norole@test.com',
            'password' => Hash::make('password'),
        ]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($userWithoutRole);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/anggota/simpanan', $response->getTargetUrl());
    }

    /** @test */
    public function returns_default_redirect_for_unauthenticated_user()
    {
        // Mock unauthenticated state
        Auth::shouldReceive('user')->andReturn(null);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/anggota/simpanan', $response->getTargetUrl());
    }

    /** @test */
    public function response_includes_cache_control_headers()
    {
        // Create admin role and user
        $adminRole = Roles::create(['name' => 'admin']);
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $adminUser->id, 'role_id' => $adminRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($adminUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertEquals('no-cache, no-store, must-revalidate', $response->headers->get('Cache-Control'));
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
        $this->assertEquals('0', $response->headers->get('Expires'));
    }

    /** @test */
    public function handles_old_kepala_yayasan_role_name()
    {
        // Create old kepala_yayasan role name
        $kepalaYayasanRole = Roles::create(['name' => 'kepala_yayasan']);
        $kepalaYayasanUser = User::create([
            'name' => 'Kepala Yayasan User',
            'email' => 'kepala.yayasan@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $kepalaYayasanUser->id, 'role_id' => $kepalaYayasanRole->id]);

        // Mock authentication
        Auth::shouldReceive('user')->andReturn($kepalaYayasanUser);

        $response = $this->customLoginResponse->toResponse($this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/kepalayayasan/financial-report', $response->getTargetUrl());
    }
}
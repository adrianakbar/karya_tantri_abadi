<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\RedirectBasedOnRole;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RedirectBasedOnRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new RedirectBasedOnRole();
    }

    /** @test */
    public function allows_admin_user_to_access_admin_dashboard()
    {
        // Create admin role and user
        $adminRole = Roles::create(['name' => 'admin']);
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $adminUser->id, 'role_id' => $adminRole->id]);

        $this->actingAs($adminUser);

        $request = Request::create('/admin', 'GET');
        $request->setRouteResolver(function () {
            return (object) ['getName' => function() { return 'filament.admin.pages.dashboard'; }];
        });

        $next = function ($request) {
            return response('OK');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function redirects_anggota_user_from_admin_dashboard()
    {
        // Create anggota role and user
        $anggotaRole = Roles::create(['name' => 'anggota']);
        $anggotaUser = User::create([
            'name' => 'Anggota User',
            'email' => 'anggota@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $anggotaUser->id, 'role_id' => $anggotaRole->id]);

        $this->actingAs($anggotaUser);

        $request = Request::create('/admin', 'GET');
        $request->setRouteResolver(function () {
            return (object) ['getName' => function() { return 'filament.admin.pages.dashboard'; }];
        });

        $next = function ($request) {
            return response('Should not reach here');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/anggota/simpanan', $response->getTargetUrl());
    }

    /** @test */
    public function redirects_bendahara_user_from_admin_dashboard()
    {
        // Create bendahara role and user
        $bendaharaRole = Roles::create(['name' => 'bendahara']);
        $bendaharaUser = User::create([
            'name' => 'Bendahara User',
            'email' => 'bendahara@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $bendaharaUser->id, 'role_id' => $bendaharaRole->id]);

        $this->actingAs($bendaharaUser);

        $request = Request::create('/admin', 'GET');
        $request->setRouteResolver(function () {
            return (object) ['getName' => function() { return 'filament.admin.pages.dashboard'; }];
        });

        $next = function ($request) {
            return response('Should not reach here');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/bendahara', $response->getTargetUrl());
    }

    /** @test */
    public function allows_access_to_non_admin_routes()
    {
        // Create anggota role and user
        $anggotaRole = Roles::create(['name' => 'anggota']);
        $anggotaUser = User::create([
            'name' => 'Anggota User',
            'email' => 'anggota@test.com',
            'password' => Hash::make('password'),
        ]);
        UserRole::create(['user_id' => $anggotaUser->id, 'role_id' => $anggotaRole->id]);

        $this->actingAs($anggotaUser);

        $request = Request::create('/anggota/simpanan', 'GET');
        $request->setRouteResolver(function () {
            return (object) ['getName' => function() { return 'filament.anggota.resources.savings.index'; }];
        });

        $next = function ($request) {
            return response('Allowed');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('Allowed', $response->getContent());
    }

    /** @test */
    public function allows_unauthenticated_requests()
    {
        $request = Request::create('/admin', 'GET');
        $request->setRouteResolver(function () {
            return (object) ['getName' => function() { return 'filament.admin.pages.dashboard'; }];
        });

        $next = function ($request) {
            return response('Guest allowed');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals('Guest allowed', $response->getContent());
    }
}
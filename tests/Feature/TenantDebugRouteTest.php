<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Http\Middleware\IdentifyTenantByPath;
use App\Http\Middleware\SwitchTenantDatabase;
use App\Models\User;
use Tests\TestCase;

class TenantDebugRouteTest extends TestCase
{
    public function test_tenant_debug_route_returns_successful_response(): void
    {
        $response = $this->get('/tenant-debug/sample');

        $response->assertOk()
            ->assertJsonPath('tenant', 'sample')
            ->assertJsonPath('message', 'Tenant debug route reached');
    }

    public function test_tenant_debug_login_route_returns_successful_response(): void
    {
        $response = $this->get('/tenant-debug/sample/login');

        $response->assertOk()
            ->assertJsonPath('tenant', 'sample')
            ->assertJsonPath('message', 'Tenant debug login route reached');
    }

    public function test_tenant_dashboard_renders_real_content_for_authenticated_users(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Tenant User',
            'email' => 'tenant@example.com',
            'type' => UserType::EMPLOYEE,
        ]);

        $this->withoutMiddleware([
            IdentifyTenantByPath::class,
            SwitchTenantDatabase::class,
        ])->actingAs($user)
            ->get('/tenant/sample/dashboard')
            ->assertOk()
            ->assertSee('Tenant Dashboard')
            ->assertSee('Good Morning')
            ->assertSee('Quick Actions')
            ->assertSee('Logout');
    }
}

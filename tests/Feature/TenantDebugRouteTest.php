<?php

namespace Tests\Feature;

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
}

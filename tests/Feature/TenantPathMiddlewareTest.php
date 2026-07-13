<?php

namespace Tests\Feature;

use App\Http\Middleware\IdentifyTenantByPath;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantPathMiddlewareTest extends TestCase
{
    public function test_debug_route_bypasses_tenant_lookup(): void
    {
        $middleware = new IdentifyTenantByPath();

        $request = Request::create('/tenant-debug/sample', 'GET');
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($name)
                {
                    return $name === 'tenant' ? 'sample' : null;
                }
            };
        });

        $response = $middleware->handle($request, function ($request) {
            return response()->json(['ok' => true]);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"ok":true}', $response->getContent());
    }

    public function test_database_connection_failure_returns_clear_diagnostic_response(): void
    {
        $this->app['config']->set('database.default', 'missing_driver');

        $middleware = new IdentifyTenantByPath();

        $request = Request::create('/tenant/sample/login', 'GET');
        $request->setRouteResolver(function () {
            return new class {
                public function parameter($name)
                {
                    return $name === 'tenant' ? 'sample' : null;
                }

                public function getName()
                {
                    return 'tenant.login';
                }
            };
        });

        $response = $middleware->handle($request, function ($request) {
            return response()->json(['ok' => true]);
        });

        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringContainsString('tenant lookup failed', strtolower($response->getContent()));
        $this->assertStringContainsString('connection_name', strtolower($response->getContent()));
        $this->assertStringContainsString('exception_message', strtolower($response->getContent()));
    }
}

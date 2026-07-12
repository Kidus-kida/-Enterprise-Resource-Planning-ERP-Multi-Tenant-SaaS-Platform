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
}

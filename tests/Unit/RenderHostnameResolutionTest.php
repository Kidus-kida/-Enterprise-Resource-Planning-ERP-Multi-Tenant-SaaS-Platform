<?php

namespace Tests\Unit;

use App\Http\Middleware\IdentifyTenantBySubdomain;
use PHPUnit\Framework\TestCase;

class RenderHostnameResolutionTest extends TestCase
{
    public function test_render_subdomain_is_extracted_as_the_tenant_slug(): void
    {
        $middleware = new IdentifyTenantBySubdomain();
        $reflection = new \ReflectionClass($middleware);
        $method = $reflection->getMethod('extractSubdomain');
        $method->setAccessible(true);

        $this->assertSame('sample', $method->invoke($middleware, 'sample.erp-tq06.onrender.com', ['erp-tq06.onrender.com']));
        $this->assertSame('tenant-a', $method->invoke($middleware, 'tenant-a.erp-tq06.onrender.com', ['erp-tq06.onrender.com']));
        $this->assertNull($method->invoke($middleware, 'erp-tq06.onrender.com', ['erp-tq06.onrender.com']));
    }
}

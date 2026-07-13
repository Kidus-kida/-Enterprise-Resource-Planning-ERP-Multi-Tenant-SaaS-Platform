<?php

namespace Tests\Feature;

use Tests\TestCase;

class TenantLookupMiddlewareTest extends TestCase
{
    public function test_tenant_lookup_query_is_built_with_new_query(): void
    {
        $middleware = new \App\Http\Middleware\IdentifyTenantByPath();
        $reflection = new \ReflectionMethod($middleware, 'buildTenantLookupQuery');

        $query = $reflection->invoke($middleware, 'sample', 'mysql');

        $this->assertNotNull($query);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
        $this->assertStringContainsString('where', strtolower($query->toSql()));
    }
}

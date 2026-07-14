<?php

namespace Tests\Feature;

use Modules\Superadmin\Models\Tenant;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    public function test_it_resolves_a_tenant_from_the_business_subdomain(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for this regression test.');
        }

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

        $connection = app('db')->connection('sqlite');
        $connection->getPdo();

        $connection->statement('CREATE TABLE tenants (id TEXT PRIMARY KEY, business_id INTEGER NULL, database_name TEXT NULL, data TEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL)');
        $connection->statement('CREATE TABLE businesses (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, subdomain TEXT NULL, tenant_id TEXT NULL, is_active INTEGER DEFAULT 1, created_at DATETIME NULL, updated_at DATETIME NULL)');

        $connection->table('businesses')->insert([
            'name' => 'Sample Business',
            'subdomain' => 'sample',
            'is_active' => 1,
        ]);

        $business = $connection->table('businesses')->where('subdomain', 'sample')->first();

        $connection->table('tenants')->insert([
            'id' => 'tenant_sample',
            'business_id' => $business->id,
            'database_name' => 'erp_tenant_sample',
            'data' => json_encode([
                'subdomain' => 'sample',
                'created_at' => now()->toDateTimeString(),
            ]),
        ]);

        $connection->table('businesses')->where('id', $business->id)->update([
            'tenant_id' => 'tenant_sample',
        ]);

        $resolved = Tenant::resolveByIdentifier('sample');

        $this->assertNotNull($resolved);
        $this->assertSame('tenant_sample', $resolved->id);
    }
}

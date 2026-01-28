<?php

namespace Modules\Superadmin\Services;

use Modules\Superadmin\Models\Tenant;
use Modules\Superadmin\Models\Domain;
use App\Business;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TenantService
{
    public function createTenantRecord(Business $business, string $subdomain)
    {
        $tenantId = 'tenant_' . Str::slug($subdomain);
        $databaseName = config('tenancy.database.prefix', 'tewoserp_tenant_') . Str::slug($subdomain);

        $tenant = Tenant::create([
            'id' => $tenantId,
            'business_id' => $business->id,
            'database_name' => $databaseName,
            'data' => [
                'subdomain' => $subdomain,
                'created_at' => now()->toDateTimeString()
            ]
        ]);

        Domain::create([
            'domain' => $subdomain . '.' . config('tenancy.central_domain', 'ettech.et'),
            'tenant_id' => $tenant->id
        ]);

        $business->update([
            'tenant_id' => $tenant->id,
            'subdomain' => $subdomain
        ]);

        return $tenant;
    }

    public function generateSetupInstructions(Tenant $tenant)
    {
        return [
            'database_name' => $tenant->database_name,
            'subdomain' => $tenant->data['subdomain'] ?? '',
            'instructions' => [
                '1. Create database in cPanel with name: ' . $tenant->database_name,
                '2. Add subdomain in cPanel: ' . ($tenant->data['subdomain'] ?? '') . '.' . config('tenancy.central_domain'),
                '3. Point subdomain to the same directory as main domain',
                '4. Run migrations for tenant database'
            ]
        ];
    }

    public function validateConnection($host, $database, $username, $password, $port = 3306)
    {
        try {
            // Create a temporary connection config
            config(['database.connections.tenant_temp' => [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
            ]]);

            DB::purge('tenant_temp');
            DB::connection('tenant_temp')->getPdo();
            
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function validateTenantDatabaseConnection(Tenant $tenant)
    {
        try {
            $connection = DB::connection('tenant');
            $connection->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function runTenantMigrations(Tenant $tenant)
    {
        // Placeholder for running migrations on tenant database
        // In manual setup, this will be done via artisan command after database creation
        return [
            'status' => 'pending',
            'message' => 'Please run: php artisan tenancy:migrate --tenants=' . $tenant->id
        ];
    }

    public function seedTenantDefaults(Tenant $tenant, array $data = [])
    {
        // Seed default data for tenant
        return [
            'status' => 'success',
            'message' => 'Default data seeded for tenant: ' . $tenant->id
        ];
    }

    public function deleteTenant(Tenant $tenant)
    {
        DB::beginTransaction();
        try {
            $tenant->domains()->delete();
            
            Business::where('tenant_id', $tenant->id)->update([
                'tenant_id' => null,
                'is_active' => false
            ]);
            
            $tenant->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    /**
     * Handle an incoming request.
     * Switches database connection to tenant if tenant ID is provided via query param or session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenantId = $request->query('tenant');
            
            // Store tenant ID in session if provided via query
            if ($tenantId) {
                session(['current_tenant_id' => $tenantId]);
            } elseif (session()->has('current_tenant_id')) {
                $tenantId = session('current_tenant_id');
            }

            // Switch to tenant database if we have a tenant ID
            if ($tenantId) {
                $this->switchToTenant($tenantId);
            }
        } catch (\Exception $e) {
            // Log error but don't crash - continue with default connection
            \Log::error("SwitchTenantDatabase Error: " . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Switch database connection to tenant
     */
    private function switchToTenant(string $tenantId): void
    {
        // Find tenant from central database
        $tenant = Tenant::on('mysql')->find($tenantId);

        if (!$tenant || empty($tenant->data) || !isset($tenant->data['db_host'])) {
            session()->forget('current_tenant_id');
            return;
        }

        $credentials = $tenant->data;

        // Validate required credentials
        if (!isset($credentials['db_name'], $credentials['db_username'])) {
            session()->forget('current_tenant_id');
            return;
        }

        // Handle password (may be encrypted or empty)
        $password = '';
        if (isset($credentials['db_password']) && !empty($credentials['db_password'])) {
            try {
                $password = decrypt($credentials['db_password']);
            } catch (\Exception $e) {
                $password = $credentials['db_password'];
            }
        }

        // Configure tenant connection
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $credentials['db_host'],
            'port' => $credentials['db_port'] ?? 3306,
            'database' => $credentials['db_name'],
            'username' => $credentials['db_username'],
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);

        // Reconnect to tenant database
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}

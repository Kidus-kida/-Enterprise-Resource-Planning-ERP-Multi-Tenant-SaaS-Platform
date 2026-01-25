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
        // Skip for Superadmin and Diagnostic routes to prevent Session Pollution/Connection Errors
        if ($request->is('superadmin*') || $request->is('diagnostic*')) {
            return $next($request);
        }

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

        // Auto-set active company for company users & Tenant Owners
        if (auth()->check()) {
            if (auth()->user()->company_id) {
                // For assigned company users, lock them to their company
                session(['user.company_id' => auth()->user()->company_id]);
                session(['user.active_company_ids' => [auth()->user()->company_id]]);
            } elseif (auth()->user()->isTenantOwner()) {
                // For Tenant Owners, if no active company set, try to set default
                if (!session()->has('user.active_company_ids') || !session()->has('user.company_id')) {
                    try {
                        // Connection should be switched by now
                        $default = \App\Company::where('is_default', 1)->first() ?? \App\Company::first();
                        if ($default) {
                            if (!session()->has('user.company_id')) {
                                session(['user.company_id' => $default->id]);
                            }
                            if (!session()->has('user.active_company_ids')) {
                                session(['user.active_company_ids' => [$default->id]]);
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignore if table doesn't exist yet
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Switch database connection to tenant
     */
    private function switchToTenant(string $tenantId): void
    {
        try {
            // Find tenant from central database
            $tenant = Tenant::on('mysql')->find($tenantId);

            if (!$tenant || empty($tenant->data) || !isset($tenant->data['db_host'])) {
                \Log::warning("SwitchTenantDatabase: Invalid tenant data for ID: $tenantId");
                session()->forget('current_tenant_id');
                return;
            }

            $credentials = $tenant->data;

            // Validate required credentials
            if (!isset($credentials['db_name'], $credentials['db_username'])) {
                \Log::warning("SwitchTenantDatabase: Missing required credentials for tenant: $tenantId");
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

            // Test the connection before switching
            try {
                DB::purge('tenant');
                DB::reconnect('tenant');
                
                // Test with a simple query
                DB::connection('tenant')->select('SELECT 1');
                
                \Log::info("SwitchTenantDatabase: Successfully switched to tenant database: {$credentials['db_name']}");
                
            } catch (\Exception $e) {
                \Log::error("SwitchTenantDatabase: Failed to connect to tenant database: " . $e->getMessage());
                
                // Fall back to default connection
                Config::set('database.default', 'mysql');
                session()->forget('current_tenant_id');
            }
            
        } catch (\Exception $e) {
            \Log::error("SwitchTenantDatabase: Error finding tenant: " . $e->getMessage());
            session()->forget('current_tenant_id');
        }
    }
}

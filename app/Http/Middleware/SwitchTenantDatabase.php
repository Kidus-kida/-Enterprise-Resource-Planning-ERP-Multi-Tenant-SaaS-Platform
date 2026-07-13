<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Superadmin\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('superadmin*') || $request->is('diagnostic*') || $request->is('logout') || $request->is('login*') || $request->is('register*') || $request->is('forgot-password*') || $request->is('reset-password*')) {
            return $next($request);
        }

        try {
            $tenantId = $request->query('tenant');

            if ($tenantId) {
                session(['current_tenant_id' => $tenantId]);
            } else {
                $centralDomain = env('CENTRAL_DOMAIN', 'ettech.et');
                if ($request->getHost() === $centralDomain) {
                    session()->forget('current_tenant_id');
                } elseif (session()->has('current_tenant_id')) {
                    $tenantId = session('current_tenant_id');
                }
            }

            if (!$tenantId) {
                $host = $request->getHost();
                $tenantId = Cache::remember("tenant_id_for_{$host}", now()->addHours(12), function () use ($host) {
                    $domainRecord = \Modules\Superadmin\Models\Domain::where('domain', $host)->first();
                    if ($domainRecord) {
                        return $domainRecord->tenant_id;
                    }

                    $centralDomain = env('CENTRAL_DOMAIN', 'ettech.et');
                    if (\Illuminate\Support\Str::endsWith($host, '.' . $centralDomain)) {
                        $subdomain = substr($host, 0, -strlen('.' . $centralDomain));
                        $business = \App\Business::where('subdomain', $subdomain)->first();
                        if ($business && $business->tenant_id) {
                            return $business->tenant_id;
                        }
                    }

                    return null;
                });

                if ($tenantId) {
                    session(['current_tenant_id' => $tenantId]);
                }
            }

            if ($tenantId) {
                $this->switchToTenant($tenantId);
            }
        } catch (\Throwable $e) {
            Log::error('SwitchTenantDatabase failed', [
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Tenant database switch failed',
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ], 503);
        }

        if (auth()->check()) {
            if (auth()->user()->company_id) {
                session(['user.company_id' => auth()->user()->company_id]);
                session(['user.active_company_ids' => [auth()->user()->company_id]]);
            } elseif (auth()->user()->isTenantOwner()) {
                if (!session()->has('user.active_company_ids') || !session()->has('user.company_id')) {
                    try {
                        $default = \App\Company::where('is_default', 1)->first() ?? \App\Company::first();
                        if ($default) {
                            if (!session()->has('user.company_id')) {
                                session(['user.company_id' => $default->id]);
                            }
                            if (!session()->has('user.active_company_ids')) {
                                session(['user.active_company_ids' => [$default->id]]);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Unable to load tenant company context', [
                            'exception_message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if (auth()->check() && auth()->user()->type === \App\Enums\UserType::SUPERADMIN) {
            abort(403, 'Superadmins cannot access tenant routes. Please use the Superadmin panel.');
        }

        if (!session()->has('current_tenant_id') && $request->is('dashboard*') && !$request->is('superadmin*')) {
            Log::critical('Security Assertion Failed: Accessing dashboard without tenant context.', [
                'host' => $request->getHost(),
            ]);
            abort(500, 'Tenant context missing - Security Abort');
        }

        return $next($request);
    }

    private function switchToTenant(string $tenantId): void
    {
        $centralConnectionName = config('database.default', env('DB_CONNECTION', 'mysql'));

        try {
            $tenant = Cache::remember("tenant_data_{$tenantId}", now()->addHours(12), function () use ($tenantId, $centralConnectionName) {
                return Tenant::on($centralConnectionName)->find($tenantId);
            });

            if (!$tenant || empty($tenant->data) || !isset($tenant->data['db_host'])) {
                throw new \RuntimeException("Invalid tenant data for ID: {$tenantId}");
            }

            $credentials = $tenant->data;
            if (!isset($credentials['db_name'], $credentials['db_username'])) {
                throw new \RuntimeException("Missing required tenant credentials for tenant: {$tenantId}");
            }

            $password = '';
            if (isset($credentials['db_password']) && !empty($credentials['db_password'])) {
                try {
                    $password = decrypt($credentials['db_password']);
                } catch (\Throwable $e) {
                    $password = $credentials['db_password'];
                }
            }

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

            DB::purge('tenant');
            DB::reconnect('tenant');
            Config::set('auth.passwords.users.connection', 'tenant');
            DB::connection('tenant')->select('SELECT 1');

            Log::info('SwitchTenantDatabase succeeded', [
                'tenant_id' => $tenantId,
                'database_name' => $credentials['db_name'],
                'connection_name' => 'tenant',
            ]);
        } catch (\Throwable $e) {
            Config::set('database.connections.tenant', []);
            DB::purge('tenant');
            session()->forget('current_tenant_id');
            Log::error('SwitchTenantDatabase failed', [
                'tenant_id' => $tenantId,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

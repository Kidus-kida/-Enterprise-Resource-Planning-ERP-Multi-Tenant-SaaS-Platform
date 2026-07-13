<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantByPath
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($request->is('tenant-debug*')) {
                return $next($request);
            }

            $tenantSlug = $request->route('tenant');

            if (!$tenantSlug) {
                return $next($request);
            }

            $possibleIds = array_values(array_unique([
                $tenantSlug,
                'tenant_' . $tenantSlug,
                'tenant' . $tenantSlug,
                Str::slug($tenantSlug),
            ]));

            $possibleDatabaseNames = array_values(array_unique([
                $tenantSlug,
                'tenant_' . $tenantSlug,
                'tenant' . $tenantSlug,
                Str::slug($tenantSlug),
            ]));

            $tenant = Tenant::whereIn('id', $possibleIds)->first()
                ?? Tenant::whereIn('database_name', $possibleDatabaseNames)->first();

            if (!$tenant) {
                $centralConnection = config('database.connections.mysql');

                return response()->json([
                    'message' => 'Tenant not found',
                    'requested_slug' => $tenantSlug,
                    'checked_lookup' => [
                        'ids' => $possibleIds,
                        'database_names' => $possibleDatabaseNames,
                    ],
                    'central_database' => $centralConnection['database'] ?? null,
                    'central_connection' => [
                        'host' => $centralConnection['host'] ?? null,
                        'port' => $centralConnection['port'] ?? null,
                        'database' => $centralConnection['database'] ?? null,
                    ],
                ], 404);
            }

            session([
                'tenant_id' => $tenant->id,
                'current_tenant_id' => $tenant->id,
                'sticky_tenant_id' => $tenant->id,
            ]);

            $request->merge(['tenant' => $tenant->id]);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Tenant middleware failed',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}

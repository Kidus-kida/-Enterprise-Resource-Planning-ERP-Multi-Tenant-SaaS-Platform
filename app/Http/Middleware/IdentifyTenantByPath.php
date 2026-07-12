<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantByPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->route('tenant');

        if (!$tenantSlug) {
            return $next($request);
        }

        $tenant = Tenant::where('id', 'tenant_' . $tenantSlug)
            ->orWhere('database_name', $tenantSlug)
            ->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found',
                'requested_slug' => $tenantSlug,
                'checked_lookup' => [
                    'id' => 'tenant_' . $tenantSlug,
                    'database_name' => $tenantSlug,
                ],
                'central_database' => config('database.connections.mysql.database') ?? null,
            ], 404);
        }

        session([
            'tenant_id' => $tenant->id,
            'current_tenant_id' => $tenant->id,
            'sticky_tenant_id' => $tenant->id,
        ]);

        $request->merge(['tenant' => $tenant->id]);

        return $next($request);
    }
}

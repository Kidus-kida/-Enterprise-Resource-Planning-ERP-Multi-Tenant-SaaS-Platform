<?php

namespace Modules\Superadmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Superadmin\Services\SubscriptionService;

class CheckModuleAccess
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(Request $request, Closure $next, string $moduleName)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin bypass
        if ($user->type === \App\Enums\UserType::SUPERADMIN) {
            return $next($request);
        }

        $business = $user->business ?? null;

        // Dynamic Tenant Resolution: identify which business owns the current DB
        $currentDb = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        $tenantBusinessId = null;
        
        $tenantRecord = \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('tenants')
            ->where('database_name', $currentDb)
            ->first();
            
        if ($tenantRecord) {
             $tenantBusinessId = $tenantRecord->business_id;
        }

        // Prefer tenant's business ID if mismatch
        if ($tenantBusinessId && (!$business || $business->id != $tenantBusinessId)) {
             $business = \App\Business::on('mysql')->find($tenantBusinessId);
        }

        if (!$business) {
            abort(403, 'No business associated with your account.');
        }

        // Check if business has access to this module
        $hasAccess = $this->subscriptionService->checkModuleAccess($business, $moduleName);

        if (!$hasAccess) {
            abort(403, 'Your subscription plan does not include access to the ' . $moduleName . ' module.');
        }

        return $next($request);
    }
}

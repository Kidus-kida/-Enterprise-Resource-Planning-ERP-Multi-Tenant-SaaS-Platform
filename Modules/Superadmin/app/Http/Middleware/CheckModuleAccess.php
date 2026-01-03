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

        $business = $user->business ?? null;

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

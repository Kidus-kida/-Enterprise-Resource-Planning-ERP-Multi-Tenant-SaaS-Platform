<?php

namespace Modules\Superadmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Superadmin\Services\SubscriptionService;
use Carbon\Carbon;

class CheckSubscription
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get user's business
        $business = $user->business ?? null;

        if (!$business) {
            return redirect()->route('home')->with('error', 'No business associated with your account.');
        }

        // Check if business has active subscription
        $activeSubscription = $business->subscriptions()
            ->where('status', 'approved')
            ->where('end_date', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$activeSubscription) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // Store subscription in request for later use
        $request->merge(['active_subscription' => $activeSubscription]);

        return $next($request);
    }
}

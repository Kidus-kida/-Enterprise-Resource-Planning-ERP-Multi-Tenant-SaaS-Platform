<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Business;
use Modules\Superadmin\Models\Package;
use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Models\ManualPayment;
use Modules\Superadmin\Models\Tenant;
use Carbon\Carbon;

class SuperadminController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'total_businesses' => Business::count(),
            'active_businesses' => Business::where('is_active', 1)->count(),
            'total_packages' => Package::count(),
            'active_packages' => Package::where('is_active', 1)->count(),
            'total_subscriptions' => Subscription::count(),
            'approved_subscriptions' => Subscription::where('status', 'approved')->count(),
            'waiting_subscriptions' => Subscription::where('status', 'waiting')->count(),
            'active_subscriptions' => Subscription::where('status', 'approved')
                ->where('end_date', '>=', Carbon::now())
                ->count(),
            'expired_subscriptions' => Subscription::where('status', 'approved')
                ->where('end_date', '<', Carbon::now())
                ->count(),
            'total_tenants' => Tenant::count(),
            'pending_payments' => ManualPayment::where('status', 'pending')->count(),
        ];

        // Recent businesses
        $recentBusinesses = Business::with(['package', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent subscriptions
        $recentSubscriptions = Subscription::with(['business', 'package'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending approvals
        $pendingSubscriptions = Subscription::with(['business', 'package'])
            ->where('status', 'waiting')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending payments
        $pendingPayments = ManualPayment::with(['business'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Expiring soon (within 7 days)
        $expiringSoon = Subscription::with(['business', 'package'])
            ->where('status', 'approved')
            ->whereBetween('end_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('end_date', 'asc')
            ->limit(5)
            ->get();

        return view('superadmin::dashboard', compact(
            'stats',
            'recentBusinesses',
            'recentSubscriptions',
            'pendingSubscriptions',
            'pendingPayments',
            'expiringSoon'
        ));
    }
}

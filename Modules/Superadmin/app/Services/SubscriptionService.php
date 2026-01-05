<?php

namespace Modules\Superadmin\Services;

use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Models\Package;
use App\Business;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function createSubscription(Business $business, Package $package, array $additionalData = [])
    {
        $startDate = $additionalData['start_date'] ?? Carbon::now();
        $endDate = $this->calculateExpiryDate($startDate, $package);

        $subscription = Subscription::create([
            'business_id' => $business->id,
            'package_id' => $package->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'package_details' => $package->toArray(),
            'module_activation_details' => $package->custom_permissions ?? [],
            'status' => $additionalData['status'] ?? 'waiting',
            'created_id' => $additionalData['created_by'] ?? auth()->id()
        ]);

        return $subscription;
    }

    public function calculateExpiryDate($startDate, Package $package)
    {
        $start = Carbon::parse($startDate);
        
        switch ($package->interval) {
            case 'days':
                return $start->addDays($package->interval_count);
            case 'months':
                return $start->addMonths($package->interval_count);
            case 'years':
                return $start->addYears($package->interval_count);
            default:
                return $start->addMonths(1);
        }
    }

    public function checkModuleAccess(Business $business, string $moduleName)
    {
        $subscription = $business->subscriptions()
            ->where('status', 'approved')
            ->where('end_date', '>=', Carbon::now())
            ->latest()
            ->first();

        if (!$subscription) {
            return false;
        }

        $permissions = $subscription->module_activation_details ?? [];
        
        return isset($permissions[$moduleName]) && $permissions[$moduleName] === true;
    }

    public function handleExpiredSubscriptions()
    {
        $expiredSubscriptions = Subscription::where('status', 'approved')
            ->where('end_date', '<', Carbon::now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $business = $subscription->business;
            if ($business) {
                $business->update(['is_active' => false]);
            }
        }

        return $expiredSubscriptions->count();
    }

    public function renewSubscription(Subscription $subscription, Package $package = null)
    {
        $package = $package ?? $subscription->package;
        
        if (!$package) {
            throw new \Exception('Package not found for renewal');
        }

        $startDate = Carbon::now();
        $endDate = $this->calculateExpiryDate($startDate, $package);

        $newSubscription = Subscription::create([
            'business_id' => $subscription->business_id,
            'package_id' => $package->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'package_details' => $package->toArray(),
            'module_activation_details' => $package->custom_permissions ?? [],
            'status' => 'waiting',
            'created_id' => auth()->id()
        ]);

        return $newSubscription;
    }

    public function approveSubscription(Subscription $subscription)
    {
        DB::beginTransaction();
        try {
            $subscription->update(['status' => 'approved']);
            
            $business = $subscription->business;
            if ($business) {
                $business->update([
                    'is_active' => true,
                    'package_id' => $subscription->package_id
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function declineSubscription(Subscription $subscription, string $reason = null)
    {
        $subscription->update([
            'status' => 'declined'
        ]);

        return true;
    }
}

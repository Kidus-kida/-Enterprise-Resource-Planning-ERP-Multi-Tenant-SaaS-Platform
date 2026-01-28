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

        // Get user count from additional data
        $userCount = $additionalData['subscribed_user_count'] ?? null;

        // Calculate dynamic price if per-user pricing is enabled
        $packageService = new PackageService();
        $calculatedPrice = $packageService->calculateDynamicPrice($package, $userCount);

        // If package has custom_permissions defined, use them. Otherwise, grant access to all active modules.
        $moduleActivation = $package->custom_permissions ?? [];
        
        if (empty($moduleActivation)) {
            // Auto-populate with all active modules
            $activeModules = \Modules\Superadmin\Models\Module::where('is_active', 1)->get();
            foreach ($activeModules as $module) {
                $moduleActivation[$module->key] = true; // Grant access to all modules
            }
        }

        $subscription = Subscription::create([
            'business_id' => $business->id,
            'package_id' => $package->id,
            'subscribed_user_count' => $userCount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'package_details' => $package->toArray(),
            'module_activation_details' => $moduleActivation,
            'base_price' => $calculatedPrice,
            'total_price' => $calculatedPrice, // Will be updated if add-ons are added
            'status' => $additionalData['status'] ?? 'waiting',
            'created_id' => $additionalData['created_by'] ?? auth()->id(),
            'company_count' => $package->company_count
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
        
        return isset($permissions[$moduleName]) && (bool)$permissions[$moduleName];
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
            'created_id' => auth()->id(),
            'company_count' => $package->company_count
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

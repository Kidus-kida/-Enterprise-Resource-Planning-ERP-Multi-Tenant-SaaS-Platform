<?php

namespace Modules\Superadmin\Services;

use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Models\PackageAddon;
use Illuminate\Support\Facades\DB;

class AddonService
{
    public function getAvailableAddons()
    {
        return PackageAddon::active()->orderBy('sort_order')->get();
    }

    public function calculateAddonPrice(array $addonIds)
    {
        return PackageAddon::whereIn('id', $addonIds)->sum('price');
    }

    public function attachAddons(Subscription $subscription, array $addonIds)
    {
        DB::beginTransaction();
        try {
            $addons = PackageAddon::whereIn('id', $addonIds)->get();
            
            $syncData = [];
            foreach ($addons as $addon) {
                $syncData[$addon->id] = ['price_at_time' => $addon->price];
            }
            
            $subscription->addons()->sync($syncData, false); // false = don't detach existing
            
            $this->recalculateSubscriptionPrice($subscription);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function detachAddons(Subscription $subscription, array $addonIds)
    {
        DB::beginTransaction();
        try {
            $subscription->addons()->detach($addonIds);
            
            $this->recalculateSubscriptionPrice($subscription);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recalculateSubscriptionPrice(Subscription $subscription)
    {
        // Get base price from package
        $basePrice = $subscription->package ? $subscription->package->price : 0;
        
        // Calculate total addons price
        $addonsPrice = $subscription->addons->sum('pivot.price_at_time');
        
        // Update subscription
        $subscription->update([
            'base_price' => $basePrice,
            'addons_price' => $addonsPrice,
            'total_price' => $basePrice + $addonsPrice
        ]);
        
        return $subscription->fresh();
    }

    public function syncAddons(Subscription $subscription, array $addonIds)
    {
        DB::beginTransaction();
        try {
            $addons = PackageAddon::whereIn('id', $addonIds)->get();
            
            $syncData = [];
            foreach ($addons as $addon) {
                $syncData[$addon->id] = ['price_at_time' => $addon->price];
            }
            
            $subscription->addons()->sync($syncData); // This will replace all
            
            $this->recalculateSubscriptionPrice($subscription);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAddonsByModuleKeys(array $moduleKeys)
    {
        return PackageAddon::active()
            ->whereIn('module_key', $moduleKeys)
            ->orderBy('sort_order')
            ->get();
    }
}

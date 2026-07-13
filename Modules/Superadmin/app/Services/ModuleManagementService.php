<?php

namespace Modules\Superadmin\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\Module;
use Modules\Superadmin\Models\Package;
use Modules\Superadmin\Models\Subscription;
use App\Business;

class ModuleManagementService
{
    public function discoverInstalledModules(): array
    {
        $moduleNames = [];
        $modulesPath = base_path('Modules');

        if (!is_dir($modulesPath)) {
            return [];
        }

        foreach (glob($modulesPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $path) {
            $name = basename($path);

            if ($name === '.' || $name === '..' || $name === 'vendor') {
                continue;
            }

            $moduleDirectory = $path;
            $hasModuleMetadata = file_exists($moduleDirectory . DIRECTORY_SEPARATOR . 'composer.json')
                || file_exists($moduleDirectory . DIRECTORY_SEPARATOR . 'module.json')
                || is_dir($moduleDirectory . DIRECTORY_SEPARATOR . 'app');

            if ($hasModuleMetadata) {
                $moduleNames[] = $name;
            }
        }

        sort($moduleNames);

        return $moduleNames;
    }

    public function syncModulesToSuperadmin(): array
    {
        $moduleNames = $this->discoverInstalledModules();
        $created = 0;
        $updated = 0;

        foreach ($moduleNames as $moduleName) {
            $key = Str::slug($moduleName, '_');
            $record = Module::where('key', $key)->first();
            $payload = [
                'name' => $moduleName,
                'key' => $key,
                'icon' => 'la-cube',
                'routes' => [],
                'permissions' => [],
                'description' => 'Auto-discovered module',
                'is_core' => false,
                'is_active' => true,
                'sort_order' => 100,
            ];

            if ($record) {
                $record->fill($payload);
                $record->save();
                $updated++;
            } else {
                Module::create($payload);
                $created++;
            }
        }

        $this->syncPackagesWithModules();
        $this->ensureSampleTenantHasAllPackages();
        $this->clearCaches();

        return [
            'created' => $created,
            'updated' => $updated,
            'modules' => $moduleNames,
        ];
    }

    public function syncPackagesWithModules(): void
    {
        $activeModules = Module::where('is_active', 1)->get();
        $packageNames = ['Starter', 'Business', 'Professional', 'Enterprise'];

        foreach ($packageNames as $packageName) {
            $package = Package::where('name', $packageName)->first();
            if (!$package) {
                continue;
            }

            $permissions = is_array($package->custom_permissions) ? $package->custom_permissions : [];
            foreach ($activeModules as $module) {
                $permissions[$module->key] = $permissions[$module->key] ?? true;
            }

            $package->forceFill(['custom_permissions' => $permissions])->save();

            $subscriptions = Subscription::where('package_id', $package->id)
                ->where('status', 'approved')
                ->get();

            foreach ($subscriptions as $subscription) {
                $subscription->forceFill(['module_activation_details' => $permissions])->save();
            }
        }
    }

    public function ensureSampleTenantHasAllPackages(): void
    {
        $business = Business::find(2);
        if (!$business) {
            return;
        }

        $package = Package::where('is_active', 1)->orderByDesc('price')->first();
        if (!$package) {
            return;
        }

        $moduleNames = $this->discoverInstalledModules();
        $permissions = is_array($package->custom_permissions) ? $package->custom_permissions : [];
        foreach ($moduleNames as $moduleName) {
            $permissions[Str::slug($moduleName, '_')] = true;
        }

        $business->forceFill([
            'package_id' => $package->id,
            'is_active' => true,
            'enabled_modules' => $moduleNames,
            'user_count' => 0,
            'location_count' => 0,
            'product_count' => 0,
            'invoice_count' => 0,
        ])->save();

        $subscription = Subscription::where('business_id', $business->id)
            ->where('status', 'approved')
            ->latest('id')
            ->first();

        if ($subscription) {
            $subscription->forceFill([
                'package_id' => $package->id,
                'module_activation_details' => $permissions,
                'package_details' => $package->toArray(),
                'status' => 'approved',
                'end_date' => now()->addYears(10),
            ])->save();
        }
    }

    public function clearCaches(): void
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }
}

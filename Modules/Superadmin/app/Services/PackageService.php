<?php

namespace Modules\Superadmin\Services;

use Modules\Superadmin\Models\Package;

class PackageService
{
    public function calculatePackagePrice(Package $package, int $intervalCount = null)
    {
        $count = $intervalCount ?? $package->interval_count;
        return $package->price * $count;
    }

    public function getModulePermissions(Package $package)
    {
        return $package->custom_permissions ?? [];
    }

    public function updateModulePermissions(Package $package, array $permissions)
    {
        $package->update([
            'custom_permissions' => $permissions
        ]);

        return $package->fresh();
    }

    public function getActivePackages()
    {
        return Package::active()->orderBy('sort_order')->get();
    }

    public function getPublicPackages()
    {
        return Package::active()->public()->orderBy('sort_order')->get();
    }

    public function comparePackages(Package $package1, Package $package2)
    {
        return [
            'price_difference' => $package2->price - $package1->price,
            'feature_comparison' => [
                'locations' => [
                    'package1' => $package1->location_count,
                    'package2' => $package2->location_count,
                ],
                'users' => [
                    'package1' => $package1->user_count,
                    'package2' => $package2->user_count,
                ],
                'products' => [
                    'package1' => $package1->product_count,
                    'package2' => $package2->product_count,
                ],
                'invoices' => [
                    'package1' => $package1->invoice_count,
                    'package2' => $package2->invoice_count,
                ]
            ],
            'modules' => [
                'package1' => $package1->custom_permissions ?? [],
                'package2' => $package2->custom_permissions ?? []
            ]
        ];
    }

    public function createPackage(array $data)
    {
        return Package::create($data);
    }

    public function updatePackage(Package $package, array $data)
    {
        $package->update($data);
        return $package->fresh();
    }

    public function deletePackage(Package $package)
    {
        return $package->delete();
    }
}

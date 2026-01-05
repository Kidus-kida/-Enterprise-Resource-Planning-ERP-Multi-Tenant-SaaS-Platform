<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Models\Package;

class PackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for small businesses just getting started',
                'price' => 5000.00,
                'currency_id' => null, // Will use default ETB
                'interval' => 'months',
                'interval_count' => 1,
                'trial_days' => 14,
                'location_count' => 1,
                'user_count' => 5,
                'product_count' => 100,
                'invoice_count' => 50,
                'custom_permissions' => [
                    'contacts' => true,
                    'products' => true,
                    'pos' => true,
                    'purchases' => false,
                    'accounting' => false,
                    'reports' => true,
                    'hr' => false,
                    'payroll' => false,
                ],
                'is_active' => 1,
                'is_private' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'description' => 'For growing businesses with multiple locations',
                'price' => 12000.00,
                'currency_id' => null,
                'interval' => 'months',
                'interval_count' => 1,
                'trial_days' => 14,
                'location_count' => 3,
                'user_count' => 15,
                'product_count' => 500,
                'invoice_count' => 200,
                'custom_permissions' => [
                    'contacts' => true,
                    'products' => true,
                    'pos' => true,
                    'purchases' => true,
                    'accounting' => true,
                    'reports' => true,
                    'hr' => true,
                    'payroll' => false,
                ],
                'is_active' => 1,
                'is_private' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'description' => 'Advanced features for established businesses',
                'price' => 25000.00,
                'currency_id' => null,
                'interval' => 'months',
                'interval_count' => 1,
                'trial_days' => 14,
                'location_count' => 10,
                'user_count' => 50,
                'product_count' => 2000,
                'invoice_count' => 1000,
                'custom_permissions' => [
                    'contacts' => true,
                    'products' => true,
                    'pos' => true,
                    'purchases' => true,
                    'accounting' => true,
                    'reports' => true,
                    'hr' => true,
                    'payroll' => true,
                ],
                'is_active' => 1,
                'is_private' => 0,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited access for large organizations',
                'price' => 50000.00,
                'currency_id' => null,
                'interval' => 'months',
                'interval_count' => 1,
                'trial_days' => 30,
                'location_count' => 0, // 0 = unlimited
                'user_count' => 0, // 0 = unlimited
                'product_count' => 0, // 0 = unlimited
                'invoice_count' => 0, // 0 = unlimited
                'custom_permissions' => [
                    'contacts' => true,
                    'products' => true,
                    'pos' => true,
                    'purchases' => true,
                    'accounting' => true,
                    'reports' => true,
                    'hr' => true,
                    'payroll' => true,
                ],
                'is_active' => 1,
                'is_private' => 0,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::updateOrCreate(
                ['name' => $packageData['name']],
                $packageData
            );
        }

        $this->command->info('✅ Packages seeded successfully!');
    }
}

<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Contacts',
                'key' => 'contacts',
                'icon' => 'la-users',
                'description' => 'Manage customers, suppliers, and contacts',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 1,
                'routes' => ['contacts.*'],
                'permissions' => ['contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete']
            ],
            [
                'name' => 'Products',
                'key' => 'products',
                'icon' => 'la-box',
                'description' => 'Product catalog and inventory management',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 2,
                'routes' => ['products.*'],
                'permissions' => ['products.view', 'products.create', 'products.edit', 'products.delete']
            ],
            [
                'name' => 'POS',
                'key' => 'pos',
                'icon' => 'la-cash-register',
                'description' => 'Point of Sale system',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 3,
                'routes' => ['pos.*'],
                'permissions' => ['pos.sell', 'pos.view_sales']
            ],
            [
                'name' => 'Purchases',
                'key' => 'purchases',
                'icon' => 'la-shopping-cart',
                'description' => 'Purchase orders and supplier management',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 4,
                'routes' => ['purchases.*'],
                'permissions' => ['purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete']
            ],
            [
                'name' => 'Accounting',
                'key' => 'accounting',
                'icon' => 'la-calculator',
                'description' => 'Financial accounting and reporting',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 5,
                'routes' => ['accounting.*'],
                'permissions' => ['accounting.view', 'accounting.manage']
            ],
            [
                'name' => 'Reports',
                'key' => 'reports',
                'icon' => 'la-chart-bar',
                'description' => 'Business analytics and reporting',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 6,
                'routes' => ['reports.*'],
                'permissions' => ['reports.view', 'reports.export']
            ],
            [
                'name' => 'HR Management',
                'key' => 'hr',
                'icon' => 'la-user-tie',
                'description' => 'Employee and HR management',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 7,
                'routes' => ['hr.*'],
                'permissions' => ['hr.view', 'hr.manage_employees']
            ],
            [
                'name' => 'Payroll',
                'key' => 'payroll',
                'icon' => 'la-money-bill-wave',
                'description' => 'Payroll processing and management',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 8,
                'routes' => ['payroll.*'],
                'permissions' => ['payroll.view', 'payroll.process']
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['key' => $moduleData['key']],
                $moduleData
            );
        }

        $this->command->info('Modules seeded successfully!');
    }
}

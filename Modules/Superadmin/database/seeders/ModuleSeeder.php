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
                'permissions' => ['view-clients', 'create-client', 'edit-client', 'delete-client']
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
                'permissions' => ['purchase.view', 'purchase.create', 'purchase.edit', 'purchase.delete']
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
                'permissions' => ['view-taxes','view-expenses','view-estimates','view-invoices']
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
                'permissions' => [
                    'view-budgetCategories', 'view-budgets', 'view-budgetExpenses', 'view-budgetRevenues',
                    'view-accounts', 'view-journals', 'view-assets'
                ]
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
                'permissions' => ['view-reports']
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
                'permissions' => [
                    'view-employees', 'view-attendances', 'view-departments', 'view-designations',
                    'view-request', 'edit-request', 'create-annual-leave', 'create-leave-type',
                    'view-award', 'view-evaluation', 'view-evaluation-assignment',
                    'view-holidays', 'view-users', 'view-roles', 'view-backups', 'view-settings'
                ]
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
                'permissions' => ['view-PayrollAllowances', 'view-PayrollDeductions', 'view-payrolls', 'view-payslips']
            ],
            [
                'name' => 'Deposits',
                'key' => 'deposits',
                'icon' => 'la-money',
                'description' => 'Deposits Module',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 9,
                'routes' => ['deposits.*'],
                'permissions' => ['deposits_module']
            ],
            [
                'name' => 'Operations',
                'key' => 'operations',
                'icon' => 'la-briefcase',
                'description' => 'Assets, File Manager, and Tickets',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 10,
                'routes' => ['assets.*', 'folders.*', 'tickets.*'],
                'permissions' => ['view-assets', 'view-file-manager', 'view-tickets']
            ]
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

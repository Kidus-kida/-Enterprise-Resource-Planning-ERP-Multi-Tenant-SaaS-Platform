<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Models\Module;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // CORE MODULES (Included in all packages)
            [
                'name' => 'Dashboard',
                'key' => 'dashboard',
                'icon' => 'la-tachometer',
                'routes' => ['home', 'dashboard'],
                'permissions' => ['view-dashboard'],
                'description' => 'Main dashboard with analytics and quick stats',
                'is_core' => 1,
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'User Management',
                'key' => 'users',
                'icon' => 'la-users',
                'routes' => ['users.*'],
                'permissions' => ['view-users', 'create-users', 'edit-users', 'delete-users'],
                'description' => 'Manage system users and roles',
                'is_core' => 1,
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Settings',
                'key' => 'settings',
                'icon' => 'la-cog',
                'routes' => ['business-settings.*', 'settings.*'],
                'permissions' => ['view-settings', 'edit-settings'],
                'description' => 'System configuration and preferences',
                'is_core' => 1,
                'is_active' => 1,
                'sort_order' => 3,
            ],

            // CONTACTS MODULE
            [
                'name' => 'Contacts Management',
                'key' => 'contacts',
                'icon' => 'la-address-book',
                'routes' => ['contacts.*', 'customers.*', 'suppliers.*'],
                'permissions' => ['view-contacts', 'create-contacts', 'edit-contacts', 'delete-contacts'],
                'description' => 'Manage customers, suppliers, and business contacts',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 10,
            ],

            // PRODUCTS MODULE
            [
                'name' => 'Product Management',
                'key' => 'products',
                'icon' => 'la-cube',
                'routes' => ['products.*', 'categories.*', 'brands.*', 'units.*'],
                'permissions' => ['view-products', 'create-products', 'edit-products', 'delete-products'],
                'description' => 'Product catalog, categories, and inventory',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 11,
            ],

            // PURCHASES MODULE
            [
                'name' => 'Purchases',
                'key' => 'purchases',
                'icon' => 'la-shopping-cart',
                'routes' => ['purchases.*', 'purchase-orders.*'],
                'permissions' => ['view-purchases', 'create-purchases', 'edit-purchases', 'delete-purchases'],
                'description' => 'Purchase orders and supplier management',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 12,
            ],

            // SALES / POS MODULE
            [
                'name' => 'Point of Sale (POS)',
                'key' => 'pos',
                'icon' => 'la-calculator',
                'routes' => ['pos.*', 'sell.*', 'sells.*'],
                'permissions' => ['view-pos', 'create-sales', 'view-sales'],
                'description' => 'Point of sale and sales transactions',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 13,
            ],

            // ACCOUNTING MODULE
            [
                'name' => 'Accounting',
                'key' => 'accounting',
                'icon' => 'la-calculator',
                'routes' => ['accounting.*', 'accounts.*', 'transactions.*'],
                'permissions' => ['view-accounting', 'create-transactions', 'view-reports'],
                'description' => 'Chart of accounts, journal entries, and financial reports',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 14,
            ],

            // REPORTS MODULE
            [
                'name' => 'Reports',
                'key' => 'reports',
                'icon' => 'la-file-text',
                'routes' => ['reports.*'],
                'permissions' => ['view-reports', 'export-reports'],
                'description' => 'Business reports and analytics',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 15,
            ],

            // HR MODULE (Basic)
            [
                'name' => 'HR Management (Basic)',
                'key' => 'hr_basic',
                'icon' => 'la-users',
                'routes' => ['hr.*', 'employees.*', 'departments.*'],
                'permissions' => ['view-employees', 'create-employees', 'edit-employees'],
                'description' => 'Basic employee management and departments',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 20,
            ],

            // PAYROLL MODULE
            [
                'name' => 'Payroll',
                'key' => 'payroll',
                'icon' => 'la-money',
                'routes' => ['payroll.*', 'payslips.*', 'payroll-allowances.*', 'payroll-deductions.*'],
                'permissions' => ['view-payrolls', 'create-payrolls', 'view-payslips', 'view-PayrollAllowances', 'view-PayrollDeductions'],
                'description' => 'Employee payroll processing and payslips',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 21,
            ],

            // ADVANCED HR MODULE (For Add-on)
            [
                'name' => 'HR Management (Advanced)',
                'key' => 'hr_advanced',
                'icon' => 'la-users',
                'routes' => ['hr.*', 'performance.*', 'training.*', 'leave.*', 'attendance.*'],
                'permissions' => ['view-performance', 'manage-training', 'approve-leave', 'view-attendance'],
                'description' => 'Advanced HR with performance reviews, training, and leave management',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 22,
            ],

            // MULTI-CURRENCY MODULE
            [
                'name' => 'Multi-Currency',
                'key' => 'multi_currency',
                'icon' => 'la-dollar',
                'routes' => ['currencies.*', 'exchange-rates.*'],
                'permissions' => ['manage-currencies', 'manage-exchange-rates'],
                'description' => 'Multiple currency support with exchange rates',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 30,
            ],

            // CUSTOM REPORTS MODULE
            [
                'name' => 'Custom Reports Builder',
                'key' => 'custom_reports',
                'icon' => 'la-pie-chart',
                'routes' => ['custom-reports.*', 'report-builder.*'],
                'permissions' => ['create-custom-reports', 'manage-report-templates'],
                'description' => 'Build custom reports with drag-and-drop interface',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 31,
            ],

            // API ACCESS MODULE
            [
                'name' => 'API Access',
                'key' => 'api_access',
                'icon' => 'la-code',
                'routes' => ['api.*'],
                'permissions' => ['access-api', 'manage-api-keys'],
                'description' => 'REST API access for third-party integrations',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 32,
            ],

            // ADVANCED INVENTORY MODULE
            [
                'name' => 'Advanced Inventory',
                'key' => 'inventory_advanced',
                'icon' => 'la-cubes',
                'routes' => ['inventory.*', 'stock.*', 'warehouses.*'],
                'permissions' => ['manage-stock', 'view-stock-reports', 'manage-warehouses'],
                'description' => 'Advanced inventory with batch tracking and multi-warehouse',
                'is_core' => 0,
                'is_active' => 1,
                'sort_order' => 33,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['key' => $moduleData['key']],
                $moduleData
            );
        }

        $this->command->info('✅ ' . count($modules) . ' modules seeded successfully!');
    }
}

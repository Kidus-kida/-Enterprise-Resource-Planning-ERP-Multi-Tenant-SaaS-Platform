<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Models\PackageAddon;
use Modules\Superadmin\Models\Module;

class PackageAddonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'name' => 'Advanced HR Module',
                'description' => 'Enhanced HR features including performance reviews, training management, and advanced reporting',
                'price' => 3000.00,
                'module_key' => 'hr_advanced',
                'features' => [
                    'Performance Reviews',
                    'Training & Development',
                    'Advanced HR Reports',
                    'Employee Self-Service Portal'
                ],
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Payroll Plus',
                'description' => 'Advanced payroll processing with tax calculations and compliance reports',
                'price' => 2500.00,
                'module_key' => 'payroll_plus',
                'features' => [
                    'Automated Tax Calculations',
                    'Payroll Compliance Reports',
                    'Bank Integration',
                    'Custom Payslip Templates'
                ],
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Multi-Currency Support',
                'description' => 'Support for multiple currencies with real-time exchange rates',
                'price' => 1000.00,
                'module_key' => 'multi_currency',
                'features' => [
                    'Multiple Currency Support',
                    'Real-time Exchange Rates',
                    'Currency Conversion Reports',
                    'Multi-Currency Invoicing'
                ],
                'is_active' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Custom Reports Builder',
                'description' => 'Build and customize your own reports with drag-and-drop interface',
                'price' => 1500.00,
                'module_key' => 'custom_reports',
                'features' => [
                    'Drag & Drop Report Builder',
                    'Custom Fields',
                    'Export to Multiple Formats',
                    'Scheduled Report Delivery'
                ],
                'is_active' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'API Access',
                'description' => 'Full REST API access for integrations with third-party applications',
                'price' => 2000.00,
                'module_key' => 'api_access',
                'features' => [
                    'REST API Access',
                    'Webhooks',
                    'API Documentation',
                    'Rate Limiting'
                ],
                'is_active' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Advanced Inventory',
                'description' => 'Advanced inventory management with batch tracking and barcode scanning',
                'price' => 1800.00,
                'module_key' => 'inventory_advanced',
                'features' => [
                    'Batch & Lot Tracking',
                    'Barcode Scanning',
                    'Stock Transfer Management',
                    'Low Stock Alerts'
                ],
                'is_active' => 1,
                'sort_order' => 6,
            ],
        ];

        foreach ($addons as $addonData) {
            // Find module by key and set module_id
            $module = Module::where('key', $addonData['module_key'])->first();
            
            if ($module) {
                $addonData['module_id'] = $module->id;
            }
            
            PackageAddon::updateOrCreate(
                ['module_key' => $addonData['module_key']],
                $addonData
            );
        }

        $this->command->info('✅ Package add-ons seeded successfully!');
    }
}

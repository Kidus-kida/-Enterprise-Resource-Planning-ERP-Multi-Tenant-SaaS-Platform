<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\Superadmin\Models\Module;
use Illuminate\Support\Facades\DB;

class TenantPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch Modules from Master Database (connection 'mysql')
        // We use on('mysql') to ensuring we're reading from the central DB
        $modules = Module::on('mysql')->where('is_active', 1)->get();
        
        $allPermissions = [];

        foreach ($modules as $module) {
            $permissions = $module->permissions ?? [];
            
            // If permissions is a JSON string, decode it
            if (is_string($permissions)) {
                $permissions = json_decode($permissions, true) ?? [];
            }

            foreach ($permissions as $permissionName) {
                // Create or Update Permission in Tenant DB
                Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'module' => $module->key
                    ]
                );
                
                $allPermissions[] = $permissionName;
            }
        }

        // Add core business permissions that aren't module-specific
        $corePermissions = [
            'business_settings.access',
            'view-settings',
            'edit-settings'
        ];
        
        foreach ($corePermissions as $permName) {
            Permission::updateOrCreate(
                [
                    'name' => $permName,
                    'guard_name' => 'web',
                ],
                [
                    'module' => 'core'
                ]
            );
            $allPermissions[] = $permName;
        }

        // 2. Create 'Tenant Admin' Role
        $role = Role::firstOrCreate([
            'name' => 'Tenant Admin', // Distinct from 'Super Admin'
            'guard_name' => 'web'
        ]);

        // 3. Assign ALL Permissions to Tenant Admin
        // The Restriction happens at the User/Subscription level, unrelated to the role itself.
        // Or, strict approach: Only give permissions that the subscription allows?
        // BETTER: Give ALL. The middleware restricts the *modules*. If they upgrade, they instantly have access.
        $role->syncPermissions($allPermissions);
        
        // CRITICAL: Clear permission cache so changes take effect immediately
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info("Seeded " . count($allPermissions) . " permissions and created 'Tenant Admin' role.");
        $this->command->info("Permission cache cleared.");
    }
}

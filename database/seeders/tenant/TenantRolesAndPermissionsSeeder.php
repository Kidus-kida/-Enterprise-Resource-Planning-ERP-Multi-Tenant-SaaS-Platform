<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantRolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Configure Spatie to use tenant connection
        config(['permission.database_connection' => 'tenant']);
        
        // ==========================
        // CLEAR EXISTING ROLES (except those assigned to users)
        // ==========================
        
        // Get IDs of roles currently assigned to users
        $assignedRoleIds = \DB::connection('tenant')
            ->table('model_has_roles')
            ->pluck('role_id')
            ->unique()
            ->toArray();
        
        // Delete roles that are NOT assigned to any users
        if (!empty($assignedRoleIds)) {
            Role::whereNotIn('id', $assignedRoleIds)->delete();
        } else {
            // If no roles are assigned, delete all
            Role::truncate();
        }
        
        // Clear orphaned permissions
        Permission::whereNotIn('id', function($query) {
            $query->select('permission_id')
                  ->from('role_has_permissions');
        })->delete();
        
        // ==========================
        // CREATE PERMISSIONS
        // ==========================
        
        $permissions = [
            // Business Settings - Critical for Tenant Admin and Owner
            'business_settings.access',
            'business_settings.update',
            
            // User Management
            'view-users',
            'create-user',
            'edit-user',
            'delete-user',
            
            // Employee Management
            'view-employees',
            'create-employee',
            'edit-employee',
            'delete-employee',
            
            // Role & Permission Management
            'view-roles',
            'create-role',
            'edit-role',
            'delete-role',
            
            // Company Management
            'view-companies',
            'create-company',
            'edit-company',
            'delete-company',
            
            // HR Permissions
            'view-attendances',
            'create-attendance',
            'edit-attendance',
            'delete-attendance',
            'view-leaves',
            'approve-leaves',
            
            // Accounting Permissions
            'view-accounts',
            'create-account',
            'edit-account',
            'delete-account',
            'view-transactions',
            
            // Manager Permissions
            'view-reports',
            'approve-requests',
        ];
        
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }
        
        // ==========================
        // CREATE ROLES
        // ==========================
        
        // 1. Tenant Owner Role (Supreme Power)
        $tenantOwner = Role::firstOrCreate([
            'name' => 'tenant owner',
            'guard_name' => 'web'
        ]);
        $tenantOwner->syncPermissions(Permission::all()); // Grant ALL permissions
        
        // 2. Tenant Admin Role (Same as Owner)
        $tenantAdmin = Role::firstOrCreate([
            'name' => 'tenant admin',
            'guard_name' => 'web'
        ]);
        $tenantAdmin->syncPermissions(Permission::all()); // Grant ALL permissions
        
        // 3. Manager Role
        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web'
        ]);
        $manager->givePermissionTo([
            'business_settings.access',
            'view-users',
            'view-employees',
            'view-companies',
            'view-attendances',
            'view-leaves',
            'approve-leaves',
            'view-reports',
            'approve-requests',
        ]);
        
        // 4. HR Role
        $hr = Role::firstOrCreate([
            'name' => 'hr',
            'guard_name' => 'web'
        ]);
        $hr->givePermissionTo([
            'view-employees',
            'create-employee',
            'edit-employee',
            'view-attendances',
            'create-attendance',
            'edit-attendance',
            'view-leaves',
            'approve-leaves',
        ]);
        
        // 5. Accountant Role
        $accountant = Role::firstOrCreate([
            'name' => 'accountant',
            'guard_name' => 'web'
        ]);
        $accountant->givePermissionTo([
            'view-accounts',
            'create-account',
            'edit-account',
            'view-transactions',
        ]);
        
        // 6. Employee Role (Basic)
        $employee = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web'
        ]);
        $employee->givePermissionTo([
            'view-attendances', // Can view own attendance
        ]);
        
        echo "✓ Tenant roles and permissions seeded successfully.\n";
    }
}

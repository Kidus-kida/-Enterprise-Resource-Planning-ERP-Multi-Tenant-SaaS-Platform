<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-award',
            'view-award',
            'edit-award',
            'delete-award',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => 'web',
            'module' => 'award'
            ]);
        }

        // Ensure the HR role exists before assigning permissions
        $role = Role::firstOrCreate([
            'name' => 'HR',
            'guard_name' => 'web'
        ]);

        $role->givePermissionTo($permissions);
    }
}

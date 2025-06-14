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

        $role = Role::findByName('HR');
        $role->givePermissionTo($permissions);
    }
}

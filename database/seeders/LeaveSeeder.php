<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Arr;
class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByModule = [
            'leave-request' => [
                'create-request',
                'view-request',
                'edit-request',
                'delete-request',


            ],

            'annual-leave' => [
                'create-annual-leave',
                'view-annual-leave',
                'edit-annual-leave',
                'delete-annual-leave',


            ],

            'leave-type' => [
                'create-leave-type',
                'view-leave-type',
                'edit-leave-type',
                'delete-leave-type',
            ],
        ];
        foreach ($permissionsByModule as $module => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(
                    ['name' => $perm],
                    ['guard_name' => 'web', 'module' => $module],
                );
            }
        }

        $rolePermissions = [
            'HR' => Arr::flatten([
                $permissionsByModule['leave-request'],
                $permissionsByModule['annual-leave'],
                $permissionsByModule['leave-type'],
            ]),

            'Employee' => [
                // Employee can ONLY work with their own requests
                'create-request',
                'view-request',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            /** @var \Spatie\Permission\Models\Role $role */
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);          // replace / keep in sync
        }
    }
}



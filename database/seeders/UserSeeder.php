<?php

namespace Database\Seeders;

use App\Enums\MaritalStatus;
use App\Enums\UserType;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $clientRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Client', 'guard_name' => 'web']);
        $employeeRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@smarthr.com'],
            [
                'firstname' => 'Mushe',
                'lastname' => 'Abdul-Hakim',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'type' => UserType::SUPERADMIN,
                'is_active' => 1,
                'created_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        $client = User::firstOrCreate(
            ['email' => 'client@smarthr.com'],
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'type' => UserType::CLIENT,
                'is_active' => 1,
                'created_at' => now(),
            ]
        );
        $client->assignRole($clientRole);

        $employee = User::firstOrCreate(
            ['email' => 'employee@smarthr.com'],
            [
                'firstname' => 'Smart',
                'lastname' => 'Employee',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'type' => UserType::EMPLOYEE,
                'is_active' => 1,
                'created_at' => now(),
            ]
        );
        $employee->assignRole($employeeRole);
        $department = Department::firstOrCreate(
            ['name' => 'Nuclues'],
            ['location' => 'Bay Area']
        );

        $designation = Designation::firstOrCreate(
            ['name' => 'Software Developer']
        );

        EmployeeDetail::firstOrCreate(
            ['emp_id' => 'EMP-0001'],
            [
                'user_id' => $employee->id,
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'passport_no' => '1234567899',
                'passport_expiry_date' => '2024-06-30',
                'passport_tel' => '1234567899',
                'nationality' => 'Ghanain',
                'religion' => null,
                'ethnicity' => null,
                'marital_status' => MaritalStatus::SINGLE,
                'spouse_occupation' => 'no',
                'no_of_children' => 0,
                'emergency_contacts' => null,
                'date_joined' => now(),
                'dob' => '2023-01-01',
            ]
        );
    }
}

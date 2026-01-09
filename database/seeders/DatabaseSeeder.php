<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolePermissionSeeder::class,
            \Modules\Roles\Database\Seeders\RolesDatabaseSeeder::class,
            UserSeeder::class, // Add Users and assign roles
            BusinessSeeder::class,
            AccountTypesSeeder::class,
            AccountGroupsSeeder::class,
            CreateCashAccountSeeder::class,
            TaxRateSeeder::class,
            AccountingModuleSeeder::class,
            LeaveSeeder::class,
            \Modules\Superadmin\Database\Seeders\ModuleSeeder::class,
        ]);
    }
}

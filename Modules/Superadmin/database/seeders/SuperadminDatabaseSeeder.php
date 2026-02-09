<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;

class SuperadminDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PackagesTableSeeder::class,
            ModuleSeeder::class,
            PackageAddonsTableSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        $business_id = 1; // Default business

        DB::table('tax_rates')->insert([
            [
                'business_id' => $business_id,
                'name' => 'VAT 15%',
                'amount' => 15.00,
                'is_tax_group' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => $business_id,
                'name' => 'GST 18%',
                'amount' => 18.00,
                'is_tax_group' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => $business_id,
                'name' => 'No Tax',
                'amount' => 0.00,
                'is_tax_group' => 0,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('business_locations')->insert([
            [
                'business_id' => $business_id,
                'location_id' => 'LOC-001',
                'name' => 'Main Warehouse',
                'landmark' => 'Near Central Market',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1000',
                'mobile' => '+251911234567',
                'email' => 'warehouse@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'business_id' => $business_id,
                'location_id' => 'LOC-002',
                'name' => 'Branch Office',
                'landmark' => 'Bole Road',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1001',
                'mobile' => '+251911234568',
                'email' => 'branch@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

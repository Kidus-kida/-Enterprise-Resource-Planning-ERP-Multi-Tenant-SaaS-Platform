<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Assets', 'parent_account_type_id' => null],
            ['name' => 'Current Assets', 'parent_account_type_id' => null],
            ['name' => 'Fixed Assets', 'parent_account_type_id' => null],
            ['name' => 'Liabilities', 'parent_account_type_id' => null],
            ['name' => 'Current Liabilities', 'parent_account_type_id' => null],
            ['name' => 'Long Term Liabilities', 'parent_account_type_id' => null],
            ['name' => 'Equity', 'parent_account_type_id' => null],
            ['name' => 'Income', 'parent_account_type_id' => null],
            ['name' => 'Expenses', 'parent_account_type_id' => null],
        ];

        foreach ($types as $type) {
            AccountType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}

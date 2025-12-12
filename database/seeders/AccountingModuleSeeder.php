<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Account Types...');
        
        // Seed Account Types - only name column
        $accountTypes = [
            'Assets',
            'Current Assets',
            'Fixed Assets',
            'Liabilities',
            'Current Liabilities',
            'Long Term Liabilities',
            'Equity',
            'Income',
            'Expenses',
        ];

        foreach ($accountTypes as $name) {
            DB::table('account_types')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeding Account Groups...');

        // Seed Account Groups - only name column
        $accountGroups = [
            'Cash Account',
            'Bank Account',
            'Inventory',
            'Accounts Receivable',
            'Accounts Payable',
            'Equipment',
            'Building',
            'Capital',
            'Sales Revenue',
            'Operating Expenses',
        ];

        foreach ($accountGroups as $name) {
            DB::table('account_groups')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Account Types and Groups seeded successfully!');
        $this->command->info('📊 Created: 9 Account Types, 10 Account Groups');
        $this->command->info('💡 You can now add accounts manually through the UI at /accounting/account');
    }
}

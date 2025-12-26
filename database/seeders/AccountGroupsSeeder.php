<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\AccountGroup;
use Illuminate\Database\Seeder;

class AccountGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get account types
        $currentAssets = AccountType::where('name', 'Current Assets')->first();
        $fixedAssets = AccountType::where('name', 'Fixed Assets')->first();
        $currentLiabilities = AccountType::where('name', 'Current Liabilities')->first();
        $equity = AccountType::where('name', 'Equity')->first();
        $income = AccountType::where('name', 'Income')->first();
        $expenses = AccountType::where('name', 'Expenses')->first();

        $groups = [];

        if ($currentAssets) {
            $groups[] = [
                'name' => 'Cash Account',
                'account_type_id' => $currentAssets->id
            ];
            $groups[] = [
                'name' => 'Bank Account',
                'account_type_id' => $currentAssets->id
            ];
            $groups[] = [
                'name' => "Cheques in Hand (Customer's)",
                'account_type_id' => $currentAssets->id
            ];
            $groups[] = [
                'name' => 'Card',
                'account_type_id' => $currentAssets->id
            ];
            $groups[] = [
                'name' => 'Accounts Receivable',
                'account_type_id' => $currentAssets->id
            ];
        }

        if ($fixedAssets) {
            $groups[] = [
                'name' => 'Property',
                'account_type_id' => $fixedAssets->id
            ];
            $groups[] = [
                'name' => 'Equipment',
                'account_type_id' => $fixedAssets->id
            ];
            $groups[] = [
                'name' => 'Vehicles',
                'account_type_id' => $fixedAssets->id
            ];
        }

        if ($currentLiabilities) {
            $groups[] = [
                'name' => 'Accounts Payable',
                'account_type_id' => $currentLiabilities->id
            ];
            $groups[] = [
                'name' => 'Credit Card',
                'account_type_id' => $currentLiabilities->id
            ];
        }

        if ($equity) {
            $groups[] = [
                'name' => 'Owner\'s Equity',
                'account_type_id' => $equity->id
            ];
            $groups[] = [
                'name' => 'Retained Earnings',
                'account_type_id' => $equity->id
            ];
        }

        if ($income) {
            $groups[] = [
                'name' => 'Sales Income',
                'account_type_id' => $income->id
            ];
            $groups[] = [
                'name' => 'Service Income',
                'account_type_id' => $income->id
            ];
            $groups[] = [
                'name' => 'Other Income',
                'account_type_id' => $income->id
            ];
        }

        if ($expenses) {
            $groups[] = [
                'name' => 'Operating Expenses',
                'account_type_id' => $expenses->id
            ];
            $groups[] = [
                'name' => 'Salaries & Wages',
                'account_type_id' => $expenses->id
            ];
            $groups[] = [
                'name' => 'Rent',
                'account_type_id' => $expenses->id
            ];
            $groups[] = [
                'name' => 'Utilities',
                'account_type_id' => $expenses->id
            ];
            $groups[] = [
                'name' => 'COGS',
                'account_type_id' => $expenses->id
            ];
        }

        foreach ($groups as $group) {
            AccountGroup::firstOrCreate(
                ['name' => $group['name']], 
                $group
            );
        }
    }
}

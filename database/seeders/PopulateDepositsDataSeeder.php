<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateDepositsDataSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // 1. Get default account type (Current Assets - id: 1)
        $currentAssetsTypeId = 1;

        // 2. Create Account Groups
        $groups = [
            'Bank Account',
            'Cash Account',
            "Cheques in Hand (Customer's)",
            'Loans Taken',
            'Loans Given'
        ];

        foreach ($groups as $groupName) {
            $exists = DB::table('account_groups')->where('name', $groupName)->exists();
            if (!$exists) {
                DB::table('account_groups')->insert([
                    'name' => $groupName,
                    'account_type_id' => $currentAssetsTypeId,
                    'description' => $groupName . ' Group',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                echo "Created Account Group: {$groupName}\n";
            }
        }

        // 3. Get Business ID from first user
        $user = DB::table('users')->first();
        if (!$user) {
            echo "No users found. Skipping account creation.\n";
            return;
        }
        $business_id = $user->business_id;

        // 4. Create Accounts

        // Cheques in Hand
        $chequeGroup = DB::table('account_groups')->where('name', "Cheques in Hand (Customer's)")->first();
        if ($chequeGroup) {
            $chequeAccountExists = DB::table('accounts')
                ->where('business_id', $business_id)
                ->where('name', 'Cheques in Hand')
                ->exists();

            if (!$chequeAccountExists) {
                DB::table('accounts')->insert([
                    'business_id' => $business_id,
                    'name' => 'Cheques in Hand',
                    'account_number' => 'CHQ-001',
                    'account_type_id' => $currentAssetsTypeId,
                    'asset_type' => $chequeGroup->id,
                    'is_closed' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                echo "Created Account: Cheques in Hand\n";
            }
        }

        // Cash (Ensure it has asset_type set if Cash Account group exists)
        $cashGroup = DB::table('account_groups')->where('name', 'Cash Account')->first();
        if ($cashGroup) {
            $cashAccount = DB::table('accounts')
                ->where('business_id', $business_id)
                ->where('name', 'Cash')
                ->first();

            if ($cashAccount && is_null($cashAccount->asset_type)) {
                DB::table('accounts')->where('id', $cashAccount->id)->update([
                    'asset_type' => $cashGroup->id,
                    'updated_at' => $now
                ]);
                echo "Updated Account: Cash now linked to Cash Account group\n";
            } elseif (!$cashAccount) {
                DB::table('accounts')->insert([
                    'business_id' => $business_id,
                    'name' => 'Cash',
                    'account_number' => 'CASH-001',
                    'account_type_id' => $currentAssetsTypeId,
                    'asset_type' => $cashGroup->id,
                    'is_closed' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                echo "Created Account: Cash\n";
            }
        }
    }
}

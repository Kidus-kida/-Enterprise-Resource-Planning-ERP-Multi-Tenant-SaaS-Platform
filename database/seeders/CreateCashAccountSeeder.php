<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateCashAccountSeeder extends Seeder
{
    public function run()
    {
        // Determine business_id to use (user.business_id if available, else first business)
        $user = DB::table('users')->first();
        $business_id = null;
        if ($user) {
            if (\Schema::hasColumn('users', 'business_id') && $user->business_id) {
                $business_id = $user->business_id;
            }
        }
        if (! $business_id) {
            $business_id = DB::table('businesses')->value('id');
        }
        if (! $business_id) {
            echo "No business found. Please run BusinessSeeder first.\n";
            return;
        }

        // Check if Cash account already exists for this business
        $exists = DB::table('accounts')
            ->where('name', 'Cash')
            ->where('business_id', $business_id)
            ->exists();

        if ($exists) {
            echo "Cash account already exists for this business.\n";
            return;
        }

        // Check if CASH-001 account number is already taken (unique constraint)
        $accountNumberExists = DB::table('accounts')
            ->where('account_number', 'CASH-001')
            ->exists();

        if ($accountNumberExists) {
            echo "Cash account with number CASH-001 already exists in the system. Skipping.\n";
            return;
        }

        // Get or create default account type
        $accountType = DB::table('account_types')->first();

        if (!$accountType) {
            echo "No account types found. Please set up account types first.\n";
            return;
        }

        // Create Cash account
        DB::table('accounts')->insert([
            'business_id' => $business_id,
            'name' => 'Cash',
            'account_number' => 'CASH-001',
            'account_type_id' => $accountType->id,
            'is_closed' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Cash account created successfully!\n";
    }
}

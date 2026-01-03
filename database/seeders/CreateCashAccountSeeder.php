<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateCashAccountSeeder extends Seeder
{
    public function run()
    {
        // Get the first user's business_id
        $user = DB::table('users')->first();

        if (!$user) {
            echo "No users found. Please create a user first.\n";
            return;
        }

        // Check if Cash account already exists
        $exists = DB::table('accounts')
            ->where('name', 'Cash')
            ->where('business_id', $user->business_id)
            ->exists();

        if ($exists) {
            echo "Cash account already exists.\n";
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
            'business_id' => $user->business_id,
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

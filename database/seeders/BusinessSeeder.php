<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\WorldSeeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Ensure a user exists (Owner)
        $owner = User::first();
        if (!$owner) {
            $owner = User::create([
                'firstname' => 'Business',
                'lastname' => 'Owner',
                'email' => 'owner@example.com',
                'password' => bcrypt('password'),
                'is_active' => 1,
            ]);
        }

        // 2. Ensure Currency Exists (using WorldSeeder if needed)
        // Check if currencies table is empty
        if (DB::table('currencies')->count() == 0) {
            // WorldSeeder is resource intensive and may not be suitable for local test runs.
            // Insert a minimal fallback currency (ETB) to satisfy dependencies.
            DB::table('currencies')->insert([
                'code' => 'ETB',
                'name' => 'Ethiopian Birr',
                'symbol' => 'Br',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $this->command->warn('Inserted fallback ETB currency (skipped WorldSeeder).');
        }

        // Fetch ETB currency
        $currency = DB::table('currencies')->where('code', 'ETB')->first();

        if (!$currency) {
            // Fallback if ETB not found even after seeding (should typically be found)
            // Try to find ANY currency
            $currency = DB::table('currencies')->first();
        }

        $currency_id = $currency ? $currency->id : null;

        if (!$currency_id) {
            $this->command->error('No currency found! Please ensure WorldSeeder runs successfully.');
            // Manual fallback if forced (not recommended for managed tables)
            return;
        }

        // 3. Create Business
        $business_id = DB::table('businesses')->insertGetId([
            'name' => 'Tewos Support',
            'currency_id' => $currency_id,
            'start_date' => Carbon::now(),
            'tax_number_1' => '123456789',
            'tax_label_1' => 'TIN',
            'default_profit_percent' => 25,
            'owner_id' => $owner->id,
            'time_zone' => 'Africa/Addis_Ababa',
            'fy_start_month' => 1,
            'accounting_method' => 'fifo',
            'default_sales_discount' => 0,
            'sell_price_tax' => 'includes',
            'enable_product_expiry' => 0,
            'enable_tooltip' => 1,
            'enable_price_tax' => 1,
            'enable_purchase_status' => 1,
            'enable_inline_tax' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Update user business_id (if column exists)
        if (\Schema::hasColumn('users', 'business_id')) {
            $owner->business_id = $business_id;
            $owner->save();
        } else {
            DB::table('users')->where('id', $owner->id)->update(['updated_at' => Carbon::now()]);
            $this->command->warn('Skipping user.business_id update — column missing.');
        }

        // 4. Create Business Location
        $location_id = DB::table('business_locations')->insertGetId([
            'business_id' => $business_id,
            'location_id' => 'BL001',
            'name' => 'Main Branch',
            'landmark' => 'City Center',
            'country' => 'Ethiopia',
            'state' => 'Addis Ababa',
            'city' => 'Addis Ababa',
            'zip_code' => '1000',
            'mobile' => '0911223344',
            'email' => 'info@tewos.com',
            'is_active' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 5. Create Default Unit
        DB::table('units')->insert([
            'business_id' => $business_id,
            'actual_name' => 'Pieces',
            'short_name' => 'Pc(s)',
            'allow_decimal' => 0,
            'created_by' => $owner->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 6. Create Tax Rate (VAT)
        DB::table('tax_rates')->insert([
            'business_id' => $business_id,
            'name' => 'VAT 15%',
            'amount' => 15,
            'is_tax_group' => 0,
            'created_by' => $owner->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('Business, Location, Currency (linked), and Unit seeded successfully!');
    }
}

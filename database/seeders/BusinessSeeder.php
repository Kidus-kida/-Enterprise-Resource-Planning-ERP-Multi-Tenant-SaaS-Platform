<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\WorldSeeder;

use Modules\Superadmin\Models\Package;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

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
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'firstname' => 'Business',
                'lastname' => 'Owner',
                'username' => 'owner',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'is_active' => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );

        if (method_exists($owner, 'assignRole')) {
            $owner->assignRole('Super Admin');
        }

        // 2. Ensure Currency
        $currency = DB::table('currencies')->where('code', 'ETB')->first();
        if (!$currency) {
            DB::table('currencies')->insert([
                'code' => 'ETB',
                'name' => 'Ethiopian Birr',
                'symbol' => 'Br',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $currency = DB::table('currencies')->where('code', 'ETB')->first();
        }

        // 3. Subscription Package
        $enterprisePackageId = null;
        $package = null;
        if (class_exists(Package::class)) {
            $package = Package::where('name', 'Enterprise')->first();
            if ($package) {
                $enterprisePackageId = $package->id;
            }
        }

        // 4. Create Business (Landlord DB)
        $business = DB::table('businesses')->where('owner_id', $owner->id)->first();
        if (!$business) {
            $businessId = DB::table('businesses')->insertGetId([
                'name' => 'Tewos Support',
                'subdomain' => 'tewos-support',
                'tenant_id' => 1,
                'package_id' => $enterprisePackageId,
                'currency_id' => $currency->id,
                'start_date' => Carbon::now(),
                'tax_number_1' => '123456789',
                'tax_label_1' => 'TIN',
                'default_profit_percent' => 25,
                'owner_id' => $owner->id,
                'time_zone' => 'Africa/Addis_Ababa',
                'fy_start_month' => 1,
                'accounting_method' => 'fifo',
                'default_sales_discount' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_active' => 1,
            ]);
        } else {
            $businessId = $business->id;
        }

        // Link User
        if (Schema::hasColumn('users', 'business_id')) {
            $owner->business_id = $businessId;
            $owner->save();
        }

        // 5. Create Subscriptionf
        DB::table('subscriptions')->updateOrInsert(
            ['business_id' => $businessId],
            [
                'package_id' => $enterprisePackageId,
                'start_date' => Carbon::now()->toDateString(),
                'end_date' => Carbon::now()->addYear()->toDateString(),
                'status' => 'approved',
                'created_id' => $owner->id,
                'company_count' => 1,
                'module_activation_details' => $package ? json_encode($package->custom_permissions) : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Create Tenant Record (migration will be done manually from UI)
        $tenantId = 'tewos_support';
        $dbName = env('TENANT_DB_DATABASE', 'tewos_hr_tenant_test'); 
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');

        $tenantData = [
            'db_host' => $dbHost,
            'db_port' => $dbPort,
            'db_name' => $dbName,
            'db_username' => $dbUser,
            'db_password' => encrypt($dbPass),
        ];

        DB::table('tenants')->updateOrInsert(
            ['business_id' => $businessId],
            [
                'id' => $tenantId,
                'database_name' => $dbName,
                'data' => json_encode($tenantData),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Store owner details for tenant setup
        DB::table('businesses')->where('id', $businessId)->update([
            'owner_firstname' => 'Business',
            'owner_lastname' => 'Owner',
            'owner_email' => 'owner@example.com',
        ]);

        // Create domain record for tenant subdomain access
        DB::table('domains')->updateOrInsert(
            ['tenant_id' => $tenantId],
            [
                'domain' => 'tewos-support.' . parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $this->command->info('✅ Business, Tenant Record, and Subscription created!');
        $this->command->info('ℹ️  Run tenant migrations manually from Superadmin UI');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class TenantSetupSeeder extends Seeder
{
    /**
     * Seed a test user in the tenant database.
     * 
     * IMPORTANT: This seeder should be run MANUALLY after:
     * 1. Running `php artisan migrate:fresh --seed` (landlord setup)
     * 2. Running tenant migrations from the UI
     * 
     * Usage: php artisan db:seed --class=TenantSetupSeeder --database=tenant
     * 
     * @return void
     */
    public function run(): void
    {
        $this->command->info("===========================================");
        $this->command->info("Creating Test User in Tenant Database...");
        $this->command->info("===========================================");

        // Get the default business from landlord DB
        $business = DB::connection('mysql')->table('businesses')
            ->where('owner_email', 'owner@example.com')
            ->first();
        
        if (!$business) {
            $this->command->error("No default business found in landlord database!");
            $this->command->error("Please run 'php artisan migrate:fresh --seed' first.");
            return;
        }

        // Generate UUID for the user
        $tenantUserUuid = Str::uuid();

        // Create or update admin user in tenant database (HARDCODED DB NAME)
        DB::connection('mysql')->table('tewos_hr_tenant_test.users')->updateOrInsert(
            ['email' => 'owner@example.com'],
            [
                'firstname' => 'Business',
                'lastname' => 'Owner',
                'username' => 'owner',
                'password' => Hash::make('password'), // Fixed password for development
                'type' => 'admin',
                'is_active' => 1,
                'business_id' => $business->id,
                'uuid' => $tenantUserUuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get the ID for the success message
        $tenantUserId = DB::connection('mysql')->table('tewos_hr_tenant_test.users')
            ->where('email', 'owner@example.com')
            ->value('id');

        // Update landlord business with owner UUID
        DB::connection('mysql')->table('businesses')
            ->where('id', $business->id)
            ->update(['owner_user_uuid' => $tenantUserUuid]);

        $this->command->info("===========================================");
        $this->command->info("✅ Test User Created Successfully!");
        $this->command->info("Email: owner@example.com");
        $this->command->info("Password: password");
        $this->command->info("User ID: {$tenantUserId}");
        $this->command->info("UUID: {$tenantUserUuid}");
        $this->command->info("===========================================");
    }
}

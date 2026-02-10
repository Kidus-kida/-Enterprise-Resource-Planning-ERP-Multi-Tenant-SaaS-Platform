<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantManagementController extends Controller
{
    protected $tenantService;

    public function __construct(\Modules\Superadmin\Services\TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenants = \Modules\Superadmin\Models\Tenant::with('business')->latest()->paginate(20);

        $stats = [
            'total' => \Modules\Superadmin\Models\Tenant::count(),
            'active' => \App\Business::whereHas('tenant')->where('is_active', 1)->count(),
            'pending' => \App\Business::whereHas('tenant')->where('is_active', 0)->count(),
            'databases_created' => \Modules\Superadmin\Models\Tenant::whereNotNull('data')->count(),
        ];

        return view('superadmin::tenant-management.index', compact('tenants', 'stats'));
    }

    public function setupWizard($businessId)
    {
        $business = \App\Business::with(['tenant', 'subscription.package', 'package'])->findOrFail($businessId);

        \Log::info('SetupWizard Debug:', [
            'business_id' => $business->id,
            'has_subscription' => !is_null($business->subscription),
            'subscription_status' => $business->subscription->status ?? 'N/A',
            'has_package' => !is_null($business->package_id),
            'approved?' => $business->subscription && $business->subscription->status === 'approved'
        ]);

        // Allow tenant creation if business has an approved subscription OR has a package assigned
        $hasApprovedSubscription = $business->subscription && $business->subscription->status === 'approved';
        $hasPackage = !is_null($business->package_id);

        if (!$hasApprovedSubscription && !$hasPackage) {
            return redirect()->route('superadmin.businesses.show', $businessId)
                ->with('error', 'Business must have an approved subscription or assigned package first!');
        }

        $tenant = $business->tenant;

        if (!$tenant) {
            $subdomain = $business->subdomain ?: \Illuminate\Support\Str::slug($business->name);
            $tenant = $this->tenantService->createTenantRecord($business, $subdomain);
        }

        $setupInstructions = $this->tenantService->generateSetupInstructions($tenant);

        return view('superadmin::tenant-management.setup-wizard', compact('business', 'tenant', 'setupInstructions'));
    }

    public function verifyConnection(Request $request, $tenantId)
    {
        $tenant = \Modules\Superadmin\Models\Tenant::findOrFail($tenantId);

        $validated = $request->validate([
            'database_name' => 'required|string',
            'database_host' => 'required|string',
            'database_port' => 'required|numeric',
            'database_username' => 'required|string',
            'database_password' => 'nullable|string',
        ]);

        try {
            $result = $this->tenantService->validateConnection(
                $validated['database_host'],
                $validated['database_name'],
                $validated['database_username'],
                $validated['database_password'] ?? '',
                $validated['database_port']
            );

            if ($result['success']) {
                $tenant->update([
                    'data' => [
                        'db_host' => $validated['database_host'],
                        'db_port' => $validated['database_port'],
                        'db_name' => $validated['database_name'],
                        'db_username' => $validated['database_username'],
                        'db_password' => encrypt($validated['database_password'] ?? ''),
                    ]
                ]);

                return redirect()->back()->with('success', 'Database connection verified and saved!');
            } else {
                return redirect()->back()->with('error', 'Database connection failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Connection error: ' . $e->getMessage());
        }
    }

    public function runMigrations(Request $request, $tenantId)
    {
        // Increase execution time for heavy migration tasks
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $tenant = \Modules\Superadmin\Models\Tenant::with('business')->findOrFail($tenantId);

        if (!isset($tenant->data['db_host'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Database credentials not configured!'], 400);
            }
            return redirect()->back()->with('error', 'Database credentials not configured!');
        }

        // Validate Admin User Input - CHANGED: No longer need detailed admin input as we use owner info
        // We only check if owner info exists
        if (!$tenant->business->owner_email) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Business owner information missing. Cannot create admin user.'], 400);
            }
            return redirect()->back()->with('error', 'Business owner information missing. Cannot create admin user.');
        }

        // Removed request validation for admin_* fields as they are pre-filled/fixed

        try {
            $credentials = isset($tenant->data['db_host']) ? $tenant->data : json_decode($tenant->data, true);

            // Handle if data is array or object/string due to recent changes/casts
            if (!is_array($credentials)) {
                $credentials = json_decode($tenant->data, true);
            }

            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => $credentials['db_host'],
                    'port' => $credentials['db_port'] ?? 3306,
                    'database' => $credentials['db_name'],
                    'username' => $credentials['db_username'],
                    'password' => decrypt($credentials['db_password']),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ]
            ]);

            \DB::purge('tenant');
            \DB::reconnect('tenant');

            // Start Transaction for Provisioning
            \DB::beginTransaction();

            // LOGGING START
            $debugLog = "--- DEBUG INFO ---\n";
            $debugLog .= "Host: " . config('database.connections.tenant.host') . "\n";
            $debugLog .= "Port: " . config('database.connections.tenant.port') . "\n";
            $debugLog .= "Database: " . config('database.connections.tenant.database') . "\n";
            $debugLog .= "Username: " . config('database.connections.tenant.username') . "\n";
            $debugLog .= "------------------\n\n";

            // COLLECT MIGRATION PATHS
            $migrationPaths = [];
            $migrationPaths[] = 'database/migrations'; // Root migrations

            // Add Module migrations
            $modules = \Nwidart\Modules\Facades\Module::all();
            foreach ($modules as $module) {
                $modulePath = 'Modules/' . $module->getName() . '/database/migrations';
                if (file_exists(base_path($modulePath))) {
                    $migrationPaths[] = $modulePath;
                }
            }

            $debugLog .= "Migration Paths:\n" . implode("\n", $migrationPaths) . "\n\n";

            // Use migrate:fresh to Ensure clean slate (drops existing tables like label_task from failed runs)
            \Artisan::call('migrate:fresh', [
                '--database' => 'tenant',
                '--force' => true,
                '--path' => $migrationPaths,
            ]);

            $migrationOutput = \Artisan::output();
            $debugLog .= "Migration Command Output:\n" . ($migrationOutput ?: "(No output captured)") . "\n\n";

            // VERIFY TABLES
            try {
                $tables = \DB::connection('tenant')->select('SHOW TABLES');
                $debugLog .= "--- VERIFICATION ---\n";
                $debugLog .= "Tables Found in '" . config('database.connections.tenant.database') . "': " . count($tables) . "\n";
                foreach ($tables as $table) {
                    $tableArray = (array) $table;
                    $debugLog .= "- " . reset($tableArray) . "\n";
                }
            } catch (\Exception $e) {
                $debugLog .= "Error listing tables: " . $e->getMessage() . "\n";
            }
            // LOGGING END

            $output = $debugLog;

            // SEEDING LOGIC

            // 1. Create Business in Tenant DB to satisfy Foreign Key constraints
            // We use the same ID as the master business to keep them linked/synced
            // We do NOT use insertGetId because we want to force the ID
            \DB::connection('tenant')->table('businesses')->insert([
                'id' => $tenant->business_id,
                'name' => $tenant->business->name,
                'created_at' => now(),
                'updated_at' => now(),
                // owner_id is nullable due to our migration, so we skip it or set to null
            ]);

            // 2. Create Default Company
            \DB::connection('tenant')->table('companies')->insert([
                'business_id' => $tenant->business_id, // Link to the record we just created
                'name' => $tenant->business->name,
                'email' => $tenant->business->owner_email,
                // 'phone' => $tenant->business->owner_phone, // Removed: Column does not exist in companies table
                'is_default' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Create Admin User in Tenant DB using Owner Info
            $tenantUserUuid = \Illuminate\Support\Str::uuid(); // Generate UUID explicitly

            $tenantUserId = \DB::connection('tenant')->table('users')->insertGetId([
                'firstname' => $tenant->business->owner_firstname,
                'lastname' => $tenant->business->owner_lastname,
                'email' => $tenant->business->owner_email,
                'username' => explode('@', $tenant->business->owner_email)[0],
                'password' => \Hash::make(\Illuminate\Support\Str::random(32)), // Random password, set via email
                'type' => 'admin',
                'is_active' => 1,
                'business_id' => $tenant->business_id, // Link to central business ID
                'uuid' => $tenantUserUuid, // Insert the UUID
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // UUID is already known, no need to query it properly
            // $tenantUserUuid = ... (removed)

            // Link owner in Master DB
            $tenant->business->update([
                'owner_user_uuid' => $tenantUserUuid,
                'owner_id' => null, // Ensure this is null as per new flow
            ]);

            // RUN TENANT PERMISSION SEEDER
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\TenantPermissionSeeder',
                '--database' => 'tenant',
                '--force' => true
            ]);
            $debugLog .= "Permission Seeder Output:\n" . \Artisan::output() . "\n";

            // RUN ATTENDANCE SETTINGS SEEDER
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\AttendanceSettingsSeeder',
                '--database' => 'tenant',
                '--force' => true
            ]);
            $debugLog .= "Attendance Settings Seeder Output:\n" . \Artisan::output() . "\n";

            // ASSIGN 'Tenant Admin' ROLE
            try {
                $roleId = \DB::connection('tenant')->table('roles')->where('name', 'Tenant Admin')->value('id');
                if ($roleId) {
                    \DB::connection('tenant')->table('model_has_roles')->insert([
                        'role_id' => $roleId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $tenantUserId
                    ]);
                    $debugLog .= "Assigned 'Tenant Admin' role to user.\n";
                } else {
                    $debugLog .= "WARNING: 'Tenant Admin' role not found after seeding.\n";
                }
            } catch (\Exception $e) {
                $debugLog .= "Error assigning role: " . $e->getMessage() . "\n";
            }

            // Clear permission cache to ensure the new role assignment is recognized immediately
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // SEED DEFAULT SETTINGS (Theme & Localization)
            $now = now();
            $settings = [
                // Theme Settings
                ['group' => 'theme', 'name' => 'name', 'payload' => json_encode('Tewos HR')],
                ['group' => 'theme', 'name' => 'theme', 'payload' => json_encode('light')],
                ['group' => 'theme', 'name' => 'layout', 'payload' => json_encode('vertical')],
                ['group' => 'theme', 'name' => 'color_scheme', 'payload' => json_encode('light')],
                ['group' => 'theme', 'name' => 'layout_width', 'payload' => json_encode('fluid')],
                ['group' => 'theme', 'name' => 'layout_position', 'payload' => json_encode('fixed')],
                ['group' => 'theme', 'name' => 'topbar_color', 'payload' => json_encode('light')],
                ['group' => 'theme', 'name' => 'sidebar_size', 'payload' => json_encode('lg')],
                ['group' => 'theme', 'name' => 'sidebar_view', 'payload' => json_encode('default')],
                ['group' => 'theme', 'name' => 'sidebar_color', 'payload' => json_encode('dark')],
                ['group' => 'theme', 'name' => 'logo_light', 'payload' => json_encode('')],
                ['group' => 'theme', 'name' => 'logo_dark', 'payload' => json_encode('')],
                ['group' => 'theme', 'name' => 'favicon', 'payload' => json_encode('')],
                ['group' => 'theme', 'name' => 'sidebar_img', 'payload' => json_encode('')],

                // Localization Settings
                ['group' => 'localization', 'name' => 'country', 'payload' => json_encode('')],
                ['group' => 'localization', 'name' => 'date_format', 'payload' => json_encode('d-m-Y')],
                ['group' => 'localization', 'name' => 'timezone', 'payload' => json_encode('UTC')],
                ['group' => 'localization', 'name' => 'lang', 'payload' => json_encode('en')],
                ['group' => 'localization', 'name' => 'currency_symbol', 'payload' => json_encode('$')],
                ['group' => 'localization', 'name' => 'currency_code', 'payload' => json_encode('USD')],
            ];

            foreach ($settings as $setting) {
                \DB::connection('tenant')->table('settings')->insert(array_merge($setting, [
                    'locked' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }

            $tenant->business->update(['is_active' => 1]);

            \DB::commit();

            // Send Setup Email (After Commit)
            $this->sendOwnerSetupEmail($tenant->business);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tenant database migration and seeding completed successfully.'
                ]);
            }

            return redirect()->route('superadmin.tenant-management.setup-wizard', $tenant->business_id)
                ->with('success', 'Migrations completed! An email has been sent to the owner to set up their password.')
                ->with('migration_output', $output);

        } catch (\Exception $e) {
            \DB::rollBack();
            $output = \Artisan::output() ?? 'No output captured.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration/Seeding failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Migration/Seeding failed: ' . $e->getMessage())
                ->with('migration_output', $output);
        }
    }

    protected function sendOwnerSetupEmail($business)
    {
        try {
            // Check if email already sent recently? Maybe not needed here as it's fresh setup.

            // Manually generate and store token in TENANT DB > password_reset_tokens
            // This is required because PasswordBroker via Superadmin writes to Main DB,
            // but Tenant App looks in Tenant DB.

            // Use DatabaseTokenRepository to generate and store token in TENANT DB
            // This ensures exact compatibility with Laravel's PasswordBroker
            $connection = \DB::connection('tenant');
            $hasher = app('hash');
            $key = config('app.key');
            $table = config('auth.passwords.users.table', 'password_reset_tokens');
            $expire = config('auth.passwords.users.expire', 60);

            $tokenRepository = new \Illuminate\Auth\Passwords\DatabaseTokenRepository(
                $connection,
                $hasher,
                $table,
                $key,
                $expire
            );

            $dummyUser = new \App\Models\User();
            $dummyUser->email = $business->owner_email;

            // This creates the token, hashes it, and inserts into the table using the connection
            $token = $tokenRepository->create($dummyUser);

            \Mail::to($business->owner_email)->send(
                new \App\Mail\BusinessOwnerSetup($business, $token)
            );

            // Track successful send
            $business->update(['owner_invite_sent_at' => now()]);

            \Log::info("Owner setup email sent", ['business_id' => $business->id]);

        } catch (\Exception $e) {
            // Log but don't fail the request since provisioning succeeded
            \Log::error("Failed to send owner setup email", [
                'business_id' => $business->id,
                'error' => $e->getMessage()
            ]);

            session()->flash('warning', 'Tenant created successfully, but email failed to send. Please use "Resend Invite" button.');
        }
    }

    public function destroy($id)
    {
        try {
            $tenant = \Modules\Superadmin\Models\Tenant::with('business')->findOrFail($id);

            $tenant->business->update(['is_active' => 0]);

            $this->tenantService->deleteTenant($tenant);

            return redirect()->route('superadmin.tenant-management.index')
                ->with('success', 'Tenant deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Clear permission cache for a tenant (quick fix without running migrations)
     */
    public function clearPermissionCache($tenantId)
    {
        try {
            $tenant = \Modules\Superadmin\Models\Tenant::with('business')->findOrFail($tenantId);

            // Get database credentials
            $credentials = $tenant->data;
            if (!is_array($credentials)) {
                $credentials = json_decode($tenant->data, true);
            }

            // Configure tenant connection
            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => $credentials['db_host'],
                    'port' => $credentials['db_port'] ?? 3306,
                    'database' => $credentials['db_name'],
                    'username' => $credentials['db_username'],
                    'password' => decrypt($credentials['db_password']),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ]
            ]);

            \DB::purge('tenant');
            \DB::reconnect('tenant');

            // Clear ALL caches to ensure permission cache is reset
            // 1. Clear Spatie permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // 2. Clear application cache (where permissions are stored)
            \Cache::flush();

            // 3. Run the Artisan command for good measure
            \Artisan::call('cache:clear');

            // 4. Also clear config cache if it exists
            try {
                \Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignore if config is not cached
            }

            return redirect()->back()->with('success', 'All caches cleared successfully! The tenant user should log out and log back in, or refresh their browser.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }
}

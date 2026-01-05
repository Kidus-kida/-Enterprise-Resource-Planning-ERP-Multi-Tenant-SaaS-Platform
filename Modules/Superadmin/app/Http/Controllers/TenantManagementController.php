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
        $business = \App\Business::with(['tenant', 'subscription.package'])->findOrFail($businessId);
        
        if (!$business->subscription || $business->subscription->status !== 'approved') {
            return redirect()->route('superadmin.businesses.show', $businessId)
                ->with('error', 'Business must have an approved subscription first!');
        }
        
        $tenant = $business->tenant;
        
        if (!$tenant) {
            $tenant = $this->tenantService->createTenantRecord($business);
        }
        
        $setupInstructions = $this->tenantService->generateSetupInstructions($business, $tenant);
        
        return view('superadmin::tenant-management.setup-wizard', compact('business', 'tenant', 'setupInstructions'));
    }

    public function verifyConnection(Request $request, $tenantId)
    {
        $tenant = \Modules\Superadmin\Models\Tenant::findOrFail($tenantId);
        
        $validated = $request->validate([
            'database_name' => 'required|string',
            'database_host' => 'required|string',
            'database_username' => 'required|string',
            'database_password' => 'required|string',
        ]);
        
        try {
            $result = $this->tenantService->validateConnection(
                $validated['database_host'],
                $validated['database_name'],
                $validated['database_username'],
                $validated['database_password']
            );
            
            if ($result['success']) {
                $tenant->update([
                    'data' => json_encode([
                        'db_host' => $validated['database_host'],
                        'db_name' => $validated['database_name'],
                        'db_username' => $validated['database_username'],
                        'db_password' => encrypt($validated['database_password']),
                    ])
                ]);
                
                return redirect()->back()->with('success', 'Database connection verified and saved!');
            } else {
                return redirect()->back()->with('error', 'Database connection failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Connection error: ' . $e->getMessage());
        }
    }

    public function runMigrations($tenantId)
    {
        $tenant = \Modules\Superadmin\Models\Tenant::with('business')->findOrFail($tenantId);
        
        if (!$tenant->data) {
            return redirect()->back()->with('error', 'Database credentials not configured!');
        }
        
        try {
            $credentials = json_decode($tenant->data, true);
            
            config(['database.connections.tenant' => [
                'driver' => 'mysql',
                'host' => $credentials['db_host'],
                'database' => $credentials['db_name'],
                'username' => $credentials['db_username'],
                'password' => decrypt($credentials['db_password']),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            
            \DB::purge('tenant');
            \DB::connection('tenant')->getPdo();
            
            \Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
            ]);
            
            $tenant->business->update(['is_active' => 1]);
            
            return redirect()->route('superadmin.tenant-management.setup-wizard', $tenant->business_id)
                ->with('success', 'Migrations completed successfully! Tenant is now active.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
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
}

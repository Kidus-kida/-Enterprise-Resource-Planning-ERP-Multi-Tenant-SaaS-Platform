<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Business;
use Modules\Superadmin\Models\Package;
use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Services\SubscriptionService;

class BusinessController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index()
    {
        $businesses = Business::with(['package', 'tenant', 'subscriptions'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('superadmin::businesses.index', compact('businesses'));
    }

    public function create()
    {
        $packages = Package::active()->public()->orderBy('sort_order')->get();
        $addons = \Modules\Superadmin\Models\PackageAddon::active()->orderBy('sort_order')->get();
        return view('superadmin::businesses.create', compact('packages', 'addons'));
    }
 
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_firstname' => 'required|string|max:255',
            'owner_lastname' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:businesses,owner_email',
            'owner_phone' => 'nullable|string|max:20',
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'addons' => 'nullable|array',
            'addons.*' => 'exists:package_addons,id',
        ]);

        // Create business with owner information
        $business = Business::create([
            'name' => $validated['name'],
            'owner_email' => $validated['owner_email'],
            'owner_firstname' => $validated['owner_firstname'],
            'owner_lastname' => $validated['owner_lastname'],
            'owner_phone' => $validated['owner_phone'],
            'owner_id' => null, // Will be set after tenant user creation
            'owner_user_uuid' => null, // Will be linked after tenant user creation
            'package_id' => $validated['package_id'],
            'is_active' => 1,
            'created_by' => auth()->id(),
        ]);

        $package = Package::find($validated['package_id']);
        $subscription = $this->subscriptionService->createSubscription($business, $package, [
            'start_date' => $validated['start_date'],
            'status' => 'approved',
            'created_by' => auth()->id()
        ]);

        // Attach add-ons if selected (optional)
        if (!empty($validated['addons'])) {
            $addonService = new \Modules\Superadmin\Services\AddonService();
            $addonService->syncAddons($subscription, $validated['addons']);
        }

        return redirect()->route('superadmin.businesses.index')
            ->with('success', 'Business created successfully!');
    }

    public function show($id)
    {
        $business = Business::with(['package', 'tenant', 'subscriptions.package', 'manualPayments'])
            ->findOrFail($id);
            
        return view('superadmin::businesses.show', compact('business'));
    }

    public function edit($id)
    {
        $business = Business::with(['owner', 'subscription.addons'])->findOrFail($id);
        $packages = Package::active()->orderBy('sort_order')->get();
        $addons = \Modules\Superadmin\Models\PackageAddon::active()->orderBy('sort_order')->get();
        
        return view('superadmin::businesses.edit', compact('business', 'packages', 'addons'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $business = Business::with(['owner', 'subscription'])->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_email' => 'nullable|email|unique:businesses,owner_email,' . $id,
            'owner_firstname' => 'nullable|string|max:255',
            'owner_lastname' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'package_id' => 'nullable|exists:packages,id',
            'addons' => 'nullable|array',
            'addons.*' => 'exists:package_addons,id',
        ]);

        $business->update($validated);

        // Update subscription add-ons if business has subscription (optional)
        if ($business->subscription && $request->has('addons')) {
            $addonService = new \Modules\Superadmin\Services\AddonService();
            $addonService->syncAddons($business->subscription, $request->addons ?? []);
        }

        return redirect()->route('superadmin.businesses.index')
            ->with('success', 'Business updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $business = Business::findOrFail($id);
            
            $activeSubscription = $business->subscriptions()->where('status', 'approved')->first();
            if ($activeSubscription) {
                return redirect()->route('superadmin.businesses.index')
                    ->with('error', 'Cannot delete business with active subscription!');
            }
            
            $business->delete();
            
            return redirect()->route('superadmin.businesses.index')
                ->with('success', 'Business deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.businesses.index')
                ->with('error', 'Error deleting business: ' . $e->getMessage());
        }
    }

    public function activate($id)
    {
        $business = Business::findOrFail($id);
        $business->update(['is_active' => true]);
        
        return redirect()->route('superadmin.businesses.index')
            ->with('success', 'Business activated successfully!');
    }

    public function deactivate($id)
    {
        $business = Business::findOrFail($id);
        $business->update(['is_active' => false]);
        
        return redirect()->route('superadmin.businesses.index')
            ->with('success', 'Business deactivated successfully!');
    }

    public function resendInvite($id)
    {
        $business = Business::with('tenant')->findOrFail($id);
        
        // Check if owner has already activated
        if ($business->owner_activated_at) {
            return redirect()->back()
                ->with('error', 'Owner has already activated their account.');
        }
        
        // Check if business has owner email (new flow)
        if (!$business->owner_email) {
            return redirect()->back()
                ->with('error', 'This business was created with the old flow. No invite email available.');
        }
        
        // Check if tenant is configured
        if (!$business->tenant || !isset($business->tenant->data['db_host'])) {
             return redirect()->back()->with('error', 'Tenant database not configured yet. Cannot create password token.');
        }
        
        try {
            // Configure Tenant Connection on the fly to insert token
            $credentials = $business->tenant->data;
            if (!is_array($credentials)) {
                 $credentials = json_decode($business->tenant->data, true);
            }
            
            // Handle password decryption
            $password = '';
            if (isset($credentials['db_password']) && !empty($credentials['db_password'])) {
                try {
                    $password = decrypt($credentials['db_password']);
                } catch (\Exception $e) {
                    $password = $credentials['db_password'];
                }
            }
            
            // Setup temporary connection
            config(['database.connections.tenant_invite_temp' => [
                'driver' => 'mysql',
                'host' => $credentials['db_host'],
                'port' => $credentials['db_port'] ?? 3306,
                'database' => $credentials['db_name'],
                'username' => $credentials['db_username'],
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            
            \DB::purge('tenant_invite_temp');
            
            // Generate & Store Token in Tenant DB
            $token = \Illuminate\Support\Str::random(60);
            $hashedToken = \Illuminate\Support\Facades\Hash::make($token);
            
            \DB::connection('tenant_invite_temp')->table('password_reset_tokens')->updateOrInsert(
                ['email' => $business->owner_email],
                ['token' => $hashedToken, 'created_at' => now()]
            );
            
            // Send Email
            \Mail::to($business->owner_email)->send(
                new \App\Mail\BusinessOwnerSetup($business, $token)
            );
            
            // Track successful send
            $business->update(['owner_invite_sent_at' => now()]);
            
            \Log::info("Owner setup email resent", ['business_id' => $business->id]);
            
            return redirect()->back()
                ->with('success', 'Setup email resent successfully!');
                
        } catch (\Exception $e) {
            \Log::error("Failed to resend owner setup email", [
                'business_id' => $business->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}

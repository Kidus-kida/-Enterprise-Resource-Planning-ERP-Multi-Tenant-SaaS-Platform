<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Models\Package;
use Modules\Superadmin\Services\SubscriptionService;
use App\Business;

class SubscriptionsController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index()
    {
        $subscriptions = Subscription::with(['business', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('superadmin::subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $businesses = Business::where('is_active', 1)->orderBy('name')->get();
        $packages = Package::active()->orderBy('sort_order')->get();
        $addons = \Modules\Superadmin\Models\PackageAddon::active()->orderBy('sort_order')->get();
        
        return view('superadmin::subscriptions.create', compact('businesses', 'packages', 'addons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:business,id',
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
            'status' => 'required|in:approved,waiting,declined',
            'addons' => 'nullable|array',
            'addons.*' => 'exists:package_addons,id',
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $package = Package::findOrFail($validated['package_id']);

        $subscription = $this->subscriptionService->createSubscription($business, $package, [
            'start_date' => $validated['start_date'],
            'status' => $validated['status'],
            'created_by' => auth()->id()
        ]);

        // Attach add-ons if selected
        if (!empty($validated['addons'])) {
            $addonService = new \Modules\Superadmin\Services\AddonService();
            $addonService->syncAddons($subscription, $validated['addons']);
        } else {
            // Recalculate price even without addons
            $subscription->update([
                'base_price' => $package->price,
                'addons_price' => 0,
                'total_price' => $package->price
            ]);
        }

        return redirect()->route('superadmin.subscriptions.index')
            ->with('success', 'Subscription created successfully!');
    }

    public function show($id)
    {
        $subscription = Subscription::with(['business', 'package', 'manualPayments', 'addons'])
            ->findOrFail($id);
        
        $modules = \Modules\Superadmin\Models\Module::active()->orderBy('sort_order')->get();
            
        return view('superadmin::subscriptions.show', compact('subscription', 'modules'));
    }

    public function edit($id)
    {
        $subscription = Subscription::with(['business', 'package', 'addons'])->findOrFail($id);
        $businesses = Business::where('is_active', 1)->orderBy('name')->get();
        $packages = Package::active()->orderBy('sort_order')->get();
        $addons = \Modules\Superadmin\Models\PackageAddon::active()->orderBy('sort_order')->get();
        $selectedAddonIds = $subscription->addons->pluck('id')->toArray();
        
        return view('superadmin::subscriptions.edit', compact('subscription', 'businesses', 'packages', 'addons', 'selectedAddonIds'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $subscription = Subscription::findOrFail($id);
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:approved,waiting,declined',
            'addons' => 'nullable|array',
            'addons.*' => 'exists:package_addons,id',
        ]);

        $subscription->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status']
        ]);

        if ($request->has('sync_package')) {
            $package = $subscription->package()->first(); // Ensure fresh load
            
            \Illuminate\Support\Facades\Log::info('Syncing Permissions for Sub #' . $subscription->id, [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_permissions' => $package->custom_permissions
            ]);

            $subscription->update([
                'package_details' => $package->toArray(),
                'module_activation_details' => $package->custom_permissions ?? [],
            ]);
        }

        // Sync add-ons
        $addonService = new \Modules\Superadmin\Services\AddonService();
        if (isset($validated['addons'])) {
            $addonService->syncAddons($subscription, $validated['addons']);
        } else {
            $addonService->syncAddons($subscription, []);
        }

        if ($validated['status'] === 'approved' && $subscription->wasChanged('status')) {
            $this->subscriptionService->approveSubscription($subscription);
        }

        return redirect()->route('superadmin.subscriptions.show', $id)
            ->with('success', 'Subscription updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            if ($subscription->status === 'approved') {
                return redirect()->route('superadmin.subscriptions.index')
                    ->with('error', 'Cannot delete approved subscription!');
            }
            
            $subscription->delete();
            
            return redirect()->route('superadmin.subscriptions.index')
                ->with('success', 'Subscription deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.subscriptions.index')
                ->with('error', 'Error deleting subscription: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            if ($subscription->status === 'approved') {
                return redirect()->back()->with('error', 'Subscription is already approved!');
            }
            
            $this->subscriptionService->approveSubscription($subscription);
            
            return redirect()->route('superadmin.subscriptions.index')
                ->with('success', 'Subscription approved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving subscription: ' . $e->getMessage());
        }
    }

    public function decline(Request $request, $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            $reason = $request->input('reason', 'Declined by admin');
            $this->subscriptionService->declineSubscription($subscription, $reason);
            
            return redirect()->route('superadmin.subscriptions.index')
                ->with('success', 'Subscription declined!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error declining subscription: ' . $e->getMessage());
        }
    }

    public function renew($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            $newSubscription = $this->subscriptionService->renewSubscription($subscription);
            
            return redirect()->route('superadmin.subscriptions.show', $newSubscription->id)
                ->with('success', 'Subscription renewed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error renewing subscription: ' . $e->getMessage());
        }
    }
}

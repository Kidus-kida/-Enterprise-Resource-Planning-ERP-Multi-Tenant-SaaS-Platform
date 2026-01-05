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
        return view('superadmin::businesses.create', compact('packages'));
    }
 
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'start_date' => 'required|date',
        ]);

        $business = Business::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'owner_id' => $validated['owner_id'],
            'package_id' => $validated['package_id'],
            'is_active' => 1,
            'created_by' => auth()->id(),
        ]);

        $package = Package::find($validated['package_id']);
        $this->subscriptionService->createSubscription($business, $package, [
            'start_date' => $validated['start_date'],
            'status' => 'approved',
            'created_by' => auth()->id()
        ]);

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
        $business = Business::findOrFail($id);
        $packages = Package::active()->orderBy('sort_order')->get();
        
        return view('superadmin::businesses.edit', compact('business', 'packages'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $business = Business::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        $business->update($validated);

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
}

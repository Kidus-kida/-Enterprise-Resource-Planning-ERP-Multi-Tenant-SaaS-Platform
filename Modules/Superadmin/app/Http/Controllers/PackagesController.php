<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\Package;
use Modules\Superadmin\Services\PackageService;

class PackagesController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function index()
    {
        $packages = Package::orderBy('sort_order')->get();
        return view('superadmin::packages.index', compact('packages'));
    }

    public function create()
    {
        $modules = \Modules\Superadmin\Models\Module::active()->orderBy('sort_order')->get();
        return view('superadmin::packages.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:days,months,years',
            'interval_count' => 'required|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'location_count' => 'required|integer|min:0',
            'user_count' => 'required|integer|min:0',
            'product_count' => 'required|integer|min:0',
            'invoice_count' => 'required|integer|min:0',
            'custom_permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_private'] = $request->has('is_private') ? 1 : 0;

        $this->packageService->createPackage($validated);

        return redirect()->route('superadmin.packages.index')
            ->with('success', 'Package created successfully!');
    }

    public function show(Package $package)
    {
        $package->load('subscriptions');
        $modules = \Modules\Superadmin\Models\Module::active()->orderBy('sort_order')->get();
        return view('superadmin::packages.show', compact('package', 'modules'));
    }

    public function edit(Package $package)
    {
        $modules = \Modules\Superadmin\Models\Module::active()->orderBy('sort_order')->get();
        return view('superadmin::packages.edit', compact('package', 'modules'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages,name,' . $package->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:days,months,years',
            'interval_count' => 'required|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'location_count' => 'required|integer|min:0',
            'user_count' => 'required|integer|min:0',
            'product_count' => 'required|integer|min:0',
            'invoice_count' => 'required|integer|min:0',
            'custom_permissions' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['is_private'] = $request->has('is_private') ? 1 : 0;

        $this->packageService->updatePackage($package, $validated);

        return redirect()->route('superadmin.packages.index')
            ->with('success', 'Package updated successfully!');
    }

    public function destroy(Package $package)
    {
        try {
            $this->packageService->deletePackage($package);
            return redirect()->route('superadmin.packages.index')
                ->with('success', 'Package deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.packages.index')
                ->with('error', 'Cannot delete package: ' . $e->getMessage());
        }
    }

    public function toggleActive(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        
        $status = $package->is_active ? 'activated' : 'deactivated';
        return redirect()->route('superadmin.packages.index')
            ->with('success', "Package {$status} successfully!");
    }
}

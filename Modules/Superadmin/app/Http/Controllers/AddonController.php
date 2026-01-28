<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\PackageAddon;
use Modules\Superadmin\Models\Module;

class AddonController extends Controller
{
    public function index()
    {
        $addons = PackageAddon::with('module')->orderBy('sort_order')->paginate(20);
        
        $stats = [
            'total' => PackageAddon::count(),
            'active' => PackageAddon::active()->count(),
            'inactive' => PackageAddon::where('is_active', 0)->count(),
            'total_revenue' => PackageAddon::active()->sum('price'),
        ];
        
        return view('superadmin::addons.index', compact('addons', 'stats'));
    }

    public function create()
    {
        $modules = Module::active()->orderBy('name')->get();
        return view('superadmin::addons.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Get module_key from module
        $module = Module::findOrFail($validated['module_id']);
        $validated['module_key'] = $module->key;
        
        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features'], fn($f) => !empty($f));
        }
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        PackageAddon::create($validated);

        return redirect()->route('superadmin.addons.index')
            ->with('success', 'Add-on created successfully!');
    }

    public function show($id)
    {
        $addon = PackageAddon::with(['module', 'subscriptions.business'])->findOrFail($id);
        
        // Calculate revenue from this addon
        $totalRevenue = $addon->subscriptions()
            ->where('status', 'approved')
            ->sum('subscription_addons.price_at_time');
        
        return view('superadmin::addons.show', compact('addon', 'totalRevenue'));
    }

    public function edit($id)
    {
        $addon = PackageAddon::with('subscriptions')->findOrFail($id);
        $modules = Module::active()->orderBy('name')->get();
        
        return view('superadmin::addons.edit', compact('addon', 'modules'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $addon = PackageAddon::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Get module_key from module
        $module = Module::findOrFail($validated['module_id']);
        $validated['module_key'] = $module->key;
        
        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features'], fn($f) => !empty($f));
        }
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $addon->update($validated);

        return redirect()->route('superadmin.addons.show', $id)
            ->with('success', 'Add-on updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $addon = PackageAddon::findOrFail($id);
            
            // Check if addon has active subscriptions
            $activeSubscriptions = $addon->subscriptions()
                ->where('status', 'approved')
                ->count();
            
            if ($activeSubscriptions > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete add-on with active subscriptions!');
            }
            
            $addon->delete();
            
            return redirect()->route('superadmin.addons.index')
                ->with('success', 'Add-on deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete add-on: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        try {
            $addon = PackageAddon::findOrFail($id);
            $addon->update(['is_active' => !$addon->is_active]);
            
            $status = $addon->is_active ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "Add-on {$status} successfully!");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle add-on status: ' . $e->getMessage());
        }
    }
}

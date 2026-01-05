<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Superadmin\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::orderBy('sort_order')->paginate(20);
        
        $stats = [
            'total' => Module::count(),
            'core' => Module::core()->count(),
            'optional' => Module::optional()->count(),
            'active' => Module::active()->count(),
        ];
        
        return view('superadmin::modules.index', compact('modules', 'stats'));
    }

    public function create()
    {
        return view('superadmin::modules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:modules,key',
            'icon' => 'nullable|string|max:255',
            'routes' => 'nullable|string',
            'permissions' => 'nullable|string',
            'description' => 'nullable|string',
            'is_core' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Convert comma-separated strings to arrays
        $validated['routes'] = $validated['routes'] ? array_map('trim', explode(',', $validated['routes'])) : [];
        $validated['permissions'] = $validated['permissions'] ? array_map('trim', explode(',', $validated['permissions'])) : [];
        $validated['is_core'] = $request->has('is_core') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Module::create($validated);

        return redirect()->route('superadmin.modules.index')
            ->with('success', 'Module created successfully!');
    }

    public function show($id)
    {
        $module = Module::with('addons')->findOrFail($id);
        
        return view('superadmin::modules.show', compact('module'));
    }

    public function edit($id)
    {
        $module = Module::findOrFail($id);
        
        return view('superadmin::modules.edit', compact('module'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $module = Module::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:modules,key,' . $id,
            'icon' => 'nullable|string|max:255',
            'routes' => 'nullable|string',
            'permissions' => 'nullable|string',
            'description' => 'nullable|string',
            'is_core' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Convert comma-separated strings to arrays
        if (isset($validated['routes'])) {
            $validated['routes'] = array_map('trim', explode(',', $validated['routes']));
        }
        if (isset($validated['permissions'])) {
            $validated['permissions'] = array_map('trim', explode(',', $validated['permissions']));
        }
        $validated['is_core'] = $request->has('is_core') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $module->update($validated);

        return redirect()->route('superadmin.modules.show', $id)
            ->with('success', 'Module updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $module = Module::findOrFail($id);
            
            if ($module->is_core) {
                return redirect()->back()
                    ->with('error', 'Cannot delete core module!');
            }
            
            if ($module->addons()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete module with linked add-ons!');
            }
            
            $module->delete();
            
            return redirect()->route('superadmin.modules.index')
                ->with('success', 'Module deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        try {
            $module = Module::findOrFail($id);
            $module->update(['is_active' => !$module->is_active]);
            
            $status = $module->is_active ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "Module {$status} successfully!");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle module status: ' . $e->getMessage());
        }
    }
}

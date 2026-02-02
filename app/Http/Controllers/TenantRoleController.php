<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Models\Module;

class TenantRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::where('guard_name', 'web')
            ->where('name', '!=', 'Super Admin') // Hide Super Admin if it exists
            ->with('permissions')
            ->get();
            
        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allowedPermissions = $this->getAllowedPermissions();
        $groupedPermissions = $allowedPermissions->groupBy('module');
        
        // Fetch module details for nice labels
        $modules = Module::on('mysql')->whereIn('key', $groupedPermissions->keys())->get()
            ->keyBy('key');

        return view('pages.roles.create', compact('groupedPermissions', 'modules'));
    }

    /**
     * Reserved role names that tenants cannot use.
     */
    private function getReservedRoleNames(): array
    {
        return [
            'superadmin', 'super admin', 'super-admin', 'system owner',
            'system-owner', 'systemowner', 'root', 'administrator',
            'tenant admin', 'tenant-admin', // Allow 'Tenant Admin' itself but block variations
        ];
    }

    /**
     * Check if a role name is reserved.
     */
    private function isReservedRoleName(string $name): bool
    {
        $normalized = strtolower(trim($name));
        return in_array($normalized, $this->getReservedRoleNames());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        // SECURITY: Block reserved role names
        if ($this->isReservedRoleName($request->name)) {
            return back()->with('error', 'This role name is reserved and cannot be used.');
        }

        // Security Check: Ensure user isn't trying to assign permissions they don't own
        $allowedPermissionIds = $this->getAllowedPermissions()->pluck('id')->toArray();
        $requestedPermissions = is_array($request->permissions) ? $request->permissions : [];
        
        $validPermissions = array_intersect($requestedPermissions, $allowedPermissionIds);

        if (count($validPermissions) !== count($requestedPermissions)) {
            return back()->with('error', 'Security Warning: You cannot assign permissions not included in your subscription.');
        }

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            $role->syncPermissions($validPermissions);
            
            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'Tenant Admin' || $role->name === 'Super Admin') {
            return back()->with('error', 'System roles cannot be edited.');
        }

        $allowedPermissions = $this->getAllowedPermissions();
        $groupedPermissions = $allowedPermissions->groupBy('module');
        
        $modules = Module::on('mysql')->whereIn('key', $groupedPermissions->keys())->get()
            ->keyBy('key');
            
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('pages.roles.edit', compact('role', 'groupedPermissions', 'modules', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Tenant Admin' || $role->name === 'Super Admin') {
            return back()->with('error', 'System roles cannot be edited.');
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array'
        ]);

        // SECURITY: Block reserved role names
        if ($this->isReservedRoleName($request->name)) {
            return back()->with('error', 'This role name is reserved and cannot be used.');
        }

        $allowedPermissionIds = $this->getAllowedPermissions()->pluck('id')->toArray();
        $requestedPermissions = is_array($request->permissions) ? $request->permissions : [];
        $validPermissions = array_intersect($requestedPermissions, $allowedPermissionIds);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($validPermissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Tenant Admin' || $role->name === 'Super Admin') {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role assigned to users.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Get all permissions allowed by current subscription.
     */
    private function getAllowedPermissions()
    {
        $user = auth()->user();
        $subscription = $user->business->subscription;
        
        // If no subscription (shouldn't happen for active tenants), return empty
        if (!$subscription) return collect([]); 

        $customPermissions = $subscription->module_activation_details ?? []; // e.g. ['hr' => true]
        $allowedModules = array_keys(array_filter($customPermissions));
        
        // Also include Core modules if any
        $coreModules = Module::on('mysql')->where('is_core', 1)->pluck('key')->toArray();
        $allowedModules = array_unique(array_merge($allowedModules, $coreModules));

        // Get permissions from Tenant DB that belong to these modules
        return Permission::whereIn('module', $allowedModules)->get();
    }
}

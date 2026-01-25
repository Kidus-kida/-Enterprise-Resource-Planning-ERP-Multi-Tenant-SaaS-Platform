<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use App\DataTables\UsersDataTable;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;

class UsersController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable)
    {
        $pageTitle = __('Users');
        return $dataTable->render('pages.users.index', compact(
            'pageTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Exclude SUPERADMIN role from tenant users
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
        $companies = \App\Company::forDropdown($business_id, true);
        return view('pages.users.create',compact(
            'roles', 'companies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'middlename' => 'nullable|string',
            'lastname' => 'required',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique(User::class, 'email')],
            'password' => 'required|string|confirmed',
            'status' => 'required',
        ]);
        $imageName = null;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user = User::create([
            'business_id' => auth()->user()->business_id,
            'type' => UserType::EMPLOYEE,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'username' => $request->username,
            'address' => $request->address,
            'country' => $request->country_name,
            'country_code' => $request->country_code,
            'dial_code' => $request->dial_code,
            'phone' => $request->phone,
            'avatar' => $imageName,
            'created_by' => auth()->user()->id,
            'is_active' => !empty($request->status),
            'password' => Hash::make($request->password),
            'company_id' => !empty($request->company_id) ? $request->company_id : null
        ]);
        if($request->has('role') && !empty($request->input('role'))){
            $user->assignRole($request->role);
        }
        $notification = notify(__('User has been created'));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::withoutGlobalScope(\App\Scopes\CompanyScope::class)->findOrFail($id);
        
        // Security check: Ensure user belongs to same business (if tenant owner)
        if (auth()->user()->isTenantOwner() && $user->business_id != auth()->user()->business_id && $user->business_id != null) {
             abort(403, 'Unauthorized');
        }

        // Exclude SUPERADMIN role from tenant users
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
        $companies = \App\Company::forDropdown($business_id, true);
        
        \Log::info("EDIT DEBUG: Roles Count: " . $roles->count());
        \Log::info("EDIT DEBUG: Companies Count: " . $companies->count());
        
        $userRoles = $user->roles->pluck('name')->toArray();

        return view('pages.users.edit', compact(
            'user','roles', 'companies', 'userRoles'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::withoutGlobalScope(\App\Scopes\CompanyScope::class)->findOrFail($id);
        
        // Security check
        if (auth()->user()->isTenantOwner() && $user->business_id != auth()->user()->business_id && $user->business_id != null) {
             abort(403, 'Unauthorized');
        }

        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique(User::class, 'email')->ignore($user->id)],
            'password' => 'nullable|string|confirmed',
            'status' => 'required',
        ]);
        $imageName = $user->avatar;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user->update([
            'firstname' => $request->firstname ?? $user->firstname,
            'middlename' => $request->middlename ?? $user->middlename,
            'lastname' => $request->lastname ?? $user->lastname,
            'email' => $request->email ?? $user->email,
            'username' => $request->username ?? $user->username,
            'address' => $request->address ?? $user->address,
            'country' => $request->country_name ?? $user->country,
            'country_code' => $request->country_code ?? $user->country_code,
            'dial_code' => $request->dial_code ?? $user->dial_code,
            'phone' => $request->phone ?? $user->phone,
            'avatar' => $imageName,
            'is_active' => !empty($request->status) ?? $user->is_active,
            'password' => !empty($request->password) ? Hash::make($request->password) : $user->password,
            'company_id' => $request->has('company_id') ? (!empty($request->company_id) ? $request->company_id : null) : $user->company_id
        ]);
        if($request->has('role') && !empty($request->input('role'))){
            $user->syncRoles($request->role);
        }
        $notification = notify(__('User has been updated'));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        $notification = notify(__('User has been deleted'));
        return redirect()->route('users.index')->with($notification);
    }
}

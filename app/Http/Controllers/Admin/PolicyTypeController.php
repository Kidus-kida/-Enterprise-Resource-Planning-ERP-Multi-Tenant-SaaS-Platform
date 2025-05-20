<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PolicyType;
use App\Enums\UserType;
use Illuminate\Http\Request;
use App\DataTables\PolicyTypesDataTable;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;


class PolicyTypeController extends BaseController
{
    function __construct()
    {
        $this->middleware('permission:view-users', ['only' => ['index']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
        $this->middleware('permission:edit-user', ['only' => ['edit','update']]);
        $this->middleware('permission:create-user', ['only' => ['create', 'store']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(PolicyTypesDataTable $dataTable)
    {
        $pageTitle = __('Policy Type');
        return $dataTable->render('pages.policyType.index', compact(
            'pageTitle'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::get();
        return view('pages.policyType.create',compact(
            'roles'
        ));
    }


   
    /**
     * Store a newly created resource in storage.
     */
    public function storePolicyType(Request $request)
    {
       
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:200'
        ]);
        
        PolicyType::create([
            'name' => $request->name
        ]);
        $notification = notify(__("Policy type has been added"));
        
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
    public function edit(PolicyType $policyType)
    {
        return view('pages.policyType.edit', compact(
            'policyType'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PolicyType $policyType)
    {
        $request->validate([
            'name' => 'required',
        ]);
       
        $policyType->update([
            'name' => $request->name ?? $policyType->name
        ]);
        
        $notification = notify(__('Policy type has been updated'));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
   /**
     * Remove the specified resource from storage.
     */
    public function destroy(PolicyType $policyType)
    {
        $policyType->delete();
        $notification = notify(__('Policy Type has been deleted'));
        return redirect()->route('policy-type.index')->with($notification);
    }
}

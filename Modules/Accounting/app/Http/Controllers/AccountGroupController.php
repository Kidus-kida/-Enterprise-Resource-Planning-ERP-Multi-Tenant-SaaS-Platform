<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountGroup;
use Yajra\DataTables\Facades\DataTables;

class AccountGroupController extends Controller
{
    /**
     * Display a listing of account groups
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = AccountGroup::query();
            
            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            return DataTables::of($query->with('accountType'))
                ->addIndexColumn()
                ->addColumn('name', function ($group) {
                    return $group->name;
                })
                ->addColumn('account_type', function ($group) {
                    return $group->accountType ? $group->accountType->name : '-';
                })
                ->addColumn('description', function ($group) {
                    return $group->description ?? '-';
                })
                ->addColumn('action', function ($group) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" data-url="' . route('account-groups.edit', $group->id) . '" 
                               href="javascript:void(0)" data-ajax-modal="true" data-size="md" 
                               data-title="Edit Account Group">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#" onclick="deleteGroup(' . $group->id . ')">
                                <i class="fa-solid fa-trash m-r-5"></i> Delete
                            </a>
                        </div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::account-groups.index');
    }

    /**
     * Show the form for creating a new account group
     */
    public function create()
    {
        $business_id = session()->get('user.business_id');
        $account_types = \Modules\Accounting\Models\AccountType::where('business_id', $business_id)
                                    ->whereNull('parent_account_type_id')
                                    ->pluck('name', 'id');
        return view('accounting::account-groups.create', compact('account_types'));
    }

    /**
     * Store a newly created account group
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $business_id = session()->get('user.business_id');

            $group = new AccountGroup();
            $group->business_id = $business_id;
            $group->name = $request->name;
            $group->description = $request->description;
            $group->save();

            return response()->json([
                'success' => true,
                'message' => __('Account group created successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create account group: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified account group
     */
    public function show($id)
    {
        $group = AccountGroup::findOrFail($id);
        return view('accounting::account-groups.show', compact('group'));
    }

    /**
     * Show the form for editing the specified account group
     */
    public function edit($id)
    {
        $group = AccountGroup::findOrFail($id);
        $business_id = session()->get('user.business_id');
        $account_types = \Modules\Accounting\Models\AccountType::where('business_id', $business_id)
                                    ->whereNull('parent_account_type_id')
                                    ->pluck('name', 'id');
        return view('accounting::account-groups.create', compact('group', 'account_types'));
    }

    /**
     * Update the specified account group
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $group = AccountGroup::findOrFail($id);
            $group->name = $request->name;
            $group->description = $request->description;
            $group->save();

            return response()->json([
                'success' => true,
                'message' => __('Account group updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update account group: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified account group
     */
    public function destroy($id)
    {
        try {
            $group = AccountGroup::findOrFail($id);
            $group->delete();

            return response()->json([
                'success' => true,
                'message' => __('Account group deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete account group: ') . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountType;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;


class AccountTypeController extends Controller
{
    /**
     * Display a listing of account types
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');

            $query = AccountType::with('parent');

            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($type) {
                    return $type->name;
                })
                ->addColumn('parent_type', function ($type) {
                    return $type->parent ? $type->parent->name : '-';
                })
                ->addColumn('description', function ($type) {
                    return $type->description ?? '-';
                })
                ->addColumn('action', function ($type) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" data-url="' . route('account-types.edit', $type->id) . '" 
                               href="javascript:void(0)" data-ajax-modal="true" data-size="md" 
                               data-title="Edit Account Type">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#" onclick="deleteType(' . $type->id . ')">
                                <i class="fa-solid fa-trash m-r-5"></i> Delete
                            </a>
                        </div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::account-types.index');
    }

    /**
     * Show the form for creating a new account type
     */
    public function create()
    {
        $business_id = session()->get('user.business_id');
        // $parent_types = AccountType::where('business_id', $business_id)
        //     ->whereNull('parent_account_type_id')
        //     ->pluck('name', 'id');
        $parent_types = AccountType::all()
            // ->whereNull('parent_account_type_id')
            ->pluck('name', 'id');
            // dd($parent_types);
        return view('accounting::account-types.create', compact('parent_types'));
    }

    /**
     * Store a newly created account type
     */



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            // Get business_id, fallback to logged-in user ID
            $business_id = auth()->user()->business_id;

            if (!$business_id) {
                throw new \Exception('Business or User ID not found');
            }

            
            $type = new AccountType();
            $type->business_id = $business_id;
            $type->name = $request->name;
            $type->parent_account_type_id = $request->parent_account_type_id;
            $type->description = $request->description;
            $type->save();
            

            $output = ['success' => true,
                            'data' => $type,
                            'msg' => __("added successfully")
                        ];
        } catch (\Exception $e) {

            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        
        return $output;
    }


    /**
     * Display the specified account type
     */
    public function show($id)
    {
        $type = AccountType::findOrFail($id);
        return view('accounting::account-types.show', compact('type'));
    }

    /**
     * Show the form for editing the specified account type
     */
    public function edit($id)
    {
        $type = AccountType::findOrFail($id);
        $business_id = session()->get('user.business_id');
        // $parent_types = AccountType::where('business_id', $business_id)
        //     ->whereNull('parent_account_type_id')
        //     ->where('id', '!=', $id) // Prevent self-parenting
        //     ->pluck('name', 'id');
        $parent_types = AccountType::all()
            // ->whereNull('parent_account_type_id')
            ->where('id', '!=', $id) // Prevent self-parenting
            ->pluck('name', 'id');
        return view('accounting::account-types.create', compact('type', 'parent_types'));
    }

    /**
     * Update the specified account type
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $type = AccountType::findOrFail($id);
            $type->name = $request->name;
            $type->parent_account_type_id = $request->parent_account_type_id;
            $type->description = $request->description;
            $type->save();

            return response()->json([
                'success' => true,
                'message' => __('Account type updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update account type: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified account type
     */
    public function destroy($id)
    {
        try {
            $type = AccountType::findOrFail($id);
            $type->delete();

            return response()->json([
                'success' => true,
                'message' => __('Account type deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete account type: ') . $e->getMessage()
            ], 500);
        }
    }
}

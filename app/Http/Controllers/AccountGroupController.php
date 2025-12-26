<?php

namespace App\Http\Controllers;

use App\Models\AccountGroup;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\DataTables\AccountGroupDataTable;

class AccountGroupController extends Controller
{
    /**
     * Display a listing of account groups
     */
    public function index(AccountGroupDataTable $dataTable)
    {
        $pageTitle = __('Account Groups');
        return $dataTable->render('pages.accounting.account-groups.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new account group
     */
    public function create()
    {
        $accountTypes = AccountType::pluck('name', 'id');
        return view('pages.accounting.account-groups.create', compact('accountTypes'));
    }

    /**
     * Store a newly created account group
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:account_groups,name',
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        try {
            $group = new AccountGroup();
            $group->name = $request->name;
            $group->account_type_id = $request->account_type_id;
            $group->note = $request->note;
            $group->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Group created successfully'
                ]);
            }

            return redirect()->route('accounting.account-groups.index')->with('success', 'Account Group created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating account group: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating account group')->withInput();
        }
    }

    /**
     * Show the form for editing the specified account group
     */
    public function edit($id)
    {
        $group = AccountGroup::findOrFail($id);
        $accountTypes = AccountType::pluck('name', 'id');
        
        return view('pages.accounting.account-groups.edit', compact('group', 'accountTypes'));
    }

    /**
     * Update the specified account group
     */
    public function update(Request $request, $id)
    {
        $group = AccountGroup::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:account_groups,name,' . $id,
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        try {
            $group->name = $request->name;
            $group->account_type_id = $request->account_type_id;
            $group->note = $request->note;
            $group->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Group updated successfully'
                ]);
            }

            return redirect()->route('accounting.account-groups.index')->with('success', 'Account Group updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating account group: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating account group')->withInput();
        }
    }

    /**
     * Remove the specified account group
     */
    public function destroy($id)
    {
        try {
            $group = AccountGroup::findOrFail($id);
            
            // Check if group has accounts
            if ($group->accounts()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account group with existing accounts'
                ], 400);
            }

            $group->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account Group deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account group: ' . $e->getMessage()
            ], 500);
        }
    }
}

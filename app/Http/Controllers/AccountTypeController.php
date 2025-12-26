<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use Illuminate\Http\Request;
use App\DataTables\AccountTypeDataTable;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of account types
     */
    public function index(AccountTypeDataTable $dataTable)
    {
        $pageTitle = __('Account Types');
        return $dataTable->render('pages.accounting.account-types.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new account type
     */
    public function create()
    {
        $parentTypes = AccountType::whereNull('parent_account_type_id')->pluck('name', 'id');
        return view('pages.accounting.account-types.create', compact('parentTypes'));
    }

    /**
     * Store a newly created account type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:account_types,name',
        ]);

        try {
            $type = new AccountType();
            $type->name = $request->name;
            $type->parent_account_type_id = $request->parent_account_type_id;
            $type->note = $request->note;
            $type->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Type created successfully'
                ]);
            }

            return redirect()->route('accounting.account-types.index')->with('success', 'Account Type created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating account type: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating account type')->withInput();
        }
    }

    /**
     * Show the form for editing the specified account type
     */
    public function edit($id)
    {
        $type = AccountType::findOrFail($id);
        $parentTypes = AccountType::whereNull('parent_account_type_id')
            ->where('id', '!=', $id)
            ->pluck('name', 'id');
        
        return view('pages.accounting.account-types.edit', compact('type', 'parentTypes'));
    }

    /**
     * Update the specified account type
     */
    public function update(Request $request, $id)
    {
        $type = AccountType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:account_types,name,' . $id,
        ]);

        try {
            $type->name = $request->name;
            $type->parent_account_type_id = $request->parent_account_type_id;
            $type->note = $request->note;
            $type->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account Type updated successfully'
                ]);
            }

            return redirect()->route('accounting.account-types.index')->with('success', 'Account Type updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating account type: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating account type')->withInput();
        }
    }

    /**
     * Remove the specified account type
     */
    public function destroy($id)
    {
        try {
            $type = AccountType::findOrFail($id);
            
            // Check if type has accounts
            if ($type->accounts()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account type with existing accounts'
                ], 400);
            }

            $type->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account Type deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account type: ' . $e->getMessage()
            ], 500);
        }
    }
}

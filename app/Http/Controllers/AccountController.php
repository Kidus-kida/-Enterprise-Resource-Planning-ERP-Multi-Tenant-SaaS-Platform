<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\DataTables\AccountDataTable;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(AccountDataTable $dataTable)
    {
        $pageTitle = __('Accounts');
        $accountTypes = AccountType::pluck('name', 'id');
        $accountGroups = AccountGroup::pluck('name', 'id');
        
        return $dataTable->render('pages.accounting.accounts.index', compact(
            'pageTitle',
            'accountTypes',
            'accountGroups'
        ));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        $accountTypes = AccountType::whereNull('parent_account_type_id')->pluck('name', 'id');
        $accountGroups = AccountGroup::pluck('name', 'id');
        $parentAccounts = Account::where('is_main_account', 1)->pluck('name', 'id');

        return view('pages.accounting.accounts.create', compact('accountTypes', 'accountGroups', 'parentAccounts'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|unique:accounts,account_number',
            'account_type_id' => 'required|exists:account_types,id',
            'asset_type' => 'nullable|exists:account_groups,id',
            'opening_balance' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $account = new Account();
            $account->name = $request->name;
            $account->account_number = $request->account_number ?? $this->generateAccountNumber();
            $account->account_type_id = $request->account_type_id;
            $account->asset_type = $request->asset_type;
            $account->parent_account_id = $request->parent_account_id;
            $account->opening_balance = $request->opening_balance ?? 0;
            $account->is_main_account = $request->has('is_main_account') ? 1 : 0;
            $account->is_need_cheque = $request->is_need_cheque ?? 'N';
            $account->show_in_balance_sheet = $request->has('show_in_balance_sheet') ? 1 : 0;
            $account->note = $request->note;
            $account->created_by = Auth::id();
            $account->save();

            // Create opening balance transaction if provided
            if ($request->opening_balance && $request->opening_balance != 0) {
                $transaction = new AccountTransaction();
                $transaction->account_id = $account->id;
                $transaction->amount = abs($request->opening_balance);
                $transaction->type = $request->opening_balance > 0 ? 'debit' : 'credit';
                $transaction->sub_type = 'opening_balance';
                $transaction->operation_date = now();
                $transaction->description = 'Opening Balance';
                $transaction->created_by = Auth::id();
                $transaction->save();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account created successfully',
                    'account' => $account
                ]);
            }

            return redirect()->route('accounting.accounts.index')->with('success', 'Account created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating account: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error creating account')->withInput();
        }
    }

    /**
     * Display the specified account
     */
    public function show($id)
    {
        $account = Account::with(['accountType', 'accountGroup', 'parentAccount', 'subAccounts'])->findOrFail($id);
        $balance = Account::getAccountBalance($id);
        
        // Get recent transactions
        $transactions = AccountTransaction::where('account_id', $id)
            ->orderBy('operation_date', 'desc')
            ->paginate(20);

        return view('pages.accounting.accounts.show', compact('account', 'balance', 'transactions'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $accountTypes = AccountType::whereNull('parent_account_type_id')->pluck('name', 'id');
        $accountGroups = AccountGroup::pluck('name', 'id');
        $parentAccounts = Account::where('is_main_account', 1)
            ->where('id', '!=', $id)
            ->pluck('name', 'id');

        return view('pages.accounting.accounts.edit', compact('account', 'accountTypes', 'accountGroups', 'parentAccounts'));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|unique:accounts,account_number,' . $id,
            'account_type_id' => 'required|exists:account_types,id',
        ]);

        try {
            $account->name = $request->name;
            $account->account_number = $request->account_number;
            $account->account_type_id = $request->account_type_id;
            $account->asset_type = $request->asset_type;
            $account->parent_account_id = $request->parent_account_id;
            $account->is_main_account = $request->has('is_main_account') ? 1 : 0;
            $account->is_need_cheque = $request->is_need_cheque ?? 'N';
            $account->show_in_balance_sheet = $request->has('show_in_balance_sheet') ? 1 : 0;
            $account->note = $request->note;
            $account->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account updated successfully'
                ]);
            }

            return redirect()->route('accounting.accounts.index')->with('success', 'Account updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating account: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating account')->withInput();
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy($id)
    {
        try {
            $account = Account::findOrFail($id);
            
            // Check if account has transactions
            $hasTransactions = AccountTransaction::where('account_id', $id)->exists();
            if ($hasTransactions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account with existing transactions'
                ], 400);
            }

            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique account number
     */
    private function generateAccountNumber()
    {
        do {
            $number = 'ACC-' . strtoupper(substr(uniqid(), -8));
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }

    /**
     * Get account balance
     */
    public function getBalance($id)
    {
        $balance = Account::getAccountBalance($id);
        
        return response()->json([
            'success' => true,
            'balance' => $balance,
            'formatted_balance' => number_format($balance, 2)
        ]);
    }
}

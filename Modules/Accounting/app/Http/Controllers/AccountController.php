<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\AccountGroup;
use Modules\Accounting\Models\AccountTransaction;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = Account::with(['accountType', 'accountGroup']);
            
            // Only filter by business_id if it exists
            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            // Apply filters
            if ($request->filled('account_type')) {
                $query->where('account_type_id', $request->account_type);
            }

            if ($request->filled('account_group')) {
                $query->where('asset_type', $request->account_group);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_closed', 0);
                } elseif ($request->status === 'closed') {
                    $query->where('is_closed', 1);
                }
                // 'all' and 'disabled' options show all records
            } else {
                // Default: show only active (non-closed)
                $query->where('is_closed', 0);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('account_number', function ($account) {
                    return $account->account_number ?? 'N/A';
                })
                ->addColumn('name', function ($account) {
                    return $account->name;
                })
                ->addColumn('account_type', function ($account) {
                    return optional($account->accountType)->name ?? 'N/A';
                })
                ->addColumn('account_group', function ($account) {
                    return optional($account->accountGroup)->name ?? 'N/A';
                })
                ->addColumn('balance', function ($account) {
                    $balance = Account::getAccountBalance($account->id);
                    $class = $balance >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' . $class . '">' . number_format($balance, 2) . '</span>';
                })
                ->addColumn('status', function ($account) {
                    if ($account->is_closed) {
                        return '<span class="badge bg-danger">Closed</span>';
                    } elseif ($account->disabled) {
                        return '<span class="badge bg-warning">Disabled</span>';
                    } else {
                        return '<span class="badge bg-success">Active</span>';
                    }
                })
                ->addColumn('action', function ($account) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('account.show', $account->id) . '">
                                <i class="fa-solid fa-eye m-r-5"></i> View
                            </a>
                            <a class="dropdown-item" href="' . route('account.edit', $account->id) . '" data-ajax-modal="true" data-size="lg" data-title="Edit Account">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item" href="' . route('account.fund-transfer', $account->id) . '">
                                <i class="fa-solid fa-exchange-alt m-r-5"></i> Fund Transfer
                            </a>
                        </div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['balance', 'status', 'action'])
                ->make(true);
        }

        $account_types = AccountType::pluck('name', 'id');
        $account_groups = AccountGroup::pluck('name', 'id');

        return view('accounting::accounts.index', compact('account_types', 'account_groups'));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        $business_id = session()->get('user.business_id');
        
        $account_types = AccountType::pluck('name', 'id');
        $account_groups = AccountGroup::pluck('name', 'id');
        $parent_accounts = Account::where('is_main_account', 1)->pluck('name', 'id');

        return view('accounting::accounts.create', compact('account_types', 'account_groups', 'parent_accounts'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'asset_type' => 'required|exists:account_groups,id',
        ]);

        try {
            $business_id = session()->get('user.business_id');
            $user_id = auth()->id();

            $account = new Account();
            $account->business_id = $business_id;
            $account->name = $request->name;
            $account->account_number = $request->account_number ?? 'ACC-' . rand(100000, 999999);
            $account->account_type_id = $request->account_type_id;
            $account->asset_type = $request->asset_type;
            $account->parent_account_id = $request->parent_account_id;
            $account->opening_balance = $request->opening_balance ?? 0;
            $account->note = $request->note;
            $account->is_main_account = $request->has('is_main_account') ? 1 : 0;
            $account->is_need_cheque = $request->is_need_cheque ?? 'N';
            $account->created_by = $user_id;
            $account->save();

            return response()->json([
                'success' => true,
                'message' => __('Account created successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create account: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified account
     */
    public function show(Request $request, $id)
    {
        $account = Account::with(['accountType', 'accountGroup'])->findOrFail($id);
        
        $currentBalance = Account::getAccountBalance($id);

        if ($request->ajax()) {
            $query = AccountTransaction::where('account_id', $id)
                ->with('createdBy');

            // Apply date filters
            if ($request->filled('start_date')) {
                $query->whereDate('operation_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('operation_date', '<=', $request->end_date);
            }

            $transactions = $query->orderBy('operation_date', 'desc')->get();

            $runningBalance = 0;
            $totalDebit = 0;
            $totalCredit = 0;

            $data = $transactions->map(function ($transaction) use (&$runningBalance, &$totalDebit, &$totalCredit) {
                $debit = $transaction->type === 'debit' ? $transaction->amount : 0;
                $credit = $transaction->type === 'credit' ? $transaction->amount : 0;
                
                $totalDebit += $debit;
                $totalCredit += $credit;
                $runningBalance += ($debit - $credit);

                return [
                    'operation_date' => $transaction->operation_date->format('Y-m-d'),
                    'reference' => $transaction->transaction_id ?? 'N/A',
                    'remark' => $transaction->remark ?? '',
                    'debit' => number_format($debit, 2),
                    'credit' => number_format($credit, 2),
                    'balance' => number_format($runningBalance, 2),
                    'action' => '<a href="#" class="btn btn-sm btn-danger" onclick="deleteTransaction(' . $transaction->id . ')"><i class="fa fa-trash"></i></a>'
                ];
            });

            return response()->json([
                'data' => $data,
                'totals' => [
                    'debit' => number_format($totalDebit, 2),
                    'credit' => number_format($totalCredit, 2),
                    'balance' => number_format($runningBalance, 2),
                ]
            ]);
        }

        return view('accounting::accounts.show', compact('account', 'currentBalance'));
    }

    /**
     * Show the form for editing the specified account
     */
    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $business_id = session()->get('user.business_id');
        
        $account_types = AccountType::forDropdown($business_id);
        $account_groups = AccountGroup::forDropdown();
        $parent_accounts = Account::where('business_id', $business_id)
            ->where('is_main_account', 1)
            ->where('id', '!=', $id)
            ->pluck('name', 'id');

        return view('accounting::accounts.create', compact('account', 'account_types', 'account_groups', 'parent_accounts'));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'asset_type' => 'required|exists:account_groups,id',
        ]);

        try {
            $account = Account::findOrFail($id);
            $account->name = $request->name;
            $account->account_number = $request->account_number;
            $account->account_type_id = $request->account_type_id;
            $account->asset_type = $request->asset_type;
            $account->parent_account_id = $request->parent_account_id;
            $account->opening_balance = $request->opening_balance ?? 0;
            $account->note = $request->note;
            $account->is_main_account = $request->has('is_main_account') ? 1 : 0;
            $account->is_need_cheque = $request->is_need_cheque ?? 'N';
            $account->save();

            return response()->json([
                'success' => true,
                'message' => __('Account updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update account: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified account
     */
    public function destroy($id)
    {
        try {
            $account = Account::findOrFail($id);
            $account->delete();

            return response()->json([
                'success' => true,
                'message' => __('Account deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete account: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fund transfer form
     */
    public function getFundTransfer($id)
    {
        $account = Account::findOrFail($id);
        $business_id = session()->get('user.business_id');
        $accounts = Account::forDropdown($business_id, false, false);

        return view('accounting::accounts.fund-transfer', compact('account', 'accounts'));
    }

    /**
     * Process fund transfer
     */
    public function postFundTransfer(Request $request)
    {
        $request->validate([
            'from_account' => 'required|exists:accounts,id',
            'to_account' => 'required|exists:accounts,id|different:from_account',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $user_id = auth()->id();
            $operation_date = $request->operation_date ?? now();

            // Debit from source account
            AccountTransaction::create([
                'account_id' => $request->from_account,
                'type' => 'credit',
                'amount' => $request->amount,
                'remark' => $request->note ?? 'Fund transfer',
                'operation_date' => $operation_date,
                'created_by' => $user_id,
            ]);

            // Credit to destination account
            AccountTransaction::create([
                'account_id' => $request->to_account,
                'type' => 'debit',
                'amount' => $request->amount,
                'remark' => $request->note ?? 'Fund transfer',
                'operation_date' => $operation_date,
                'created_by' => $user_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Fund transferred successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Failed to transfer fund: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show import form
     */
    public function import()
    {
        return view('accounting::accounts.import');
    }

    /**
     * Process import
     */
    public function postImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            // TODO: Implement actual import logic with Excel/CSV processing
            return redirect()->route('account.index')
                ->with('success', __('Accounts imported successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('Import failed: ') . $e->getMessage());
        }
    }

    /**
     * Get disabled accounts
     */
    public function disabled()
    {
        return $this->index(request()->merge(['status' => 'disabled']));
    }

    /**
     * Get account dropdown for AJAX requests
     */
    public function getAccountDropdown()
    {
        $business_id = session()->get('user.business_id');
        $accounts = Account::pluck('name', 'id');

        return response()->json($accounts);
    }
}

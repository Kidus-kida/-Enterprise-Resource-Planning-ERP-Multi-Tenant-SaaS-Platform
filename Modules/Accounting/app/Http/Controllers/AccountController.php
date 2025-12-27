<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\AccountGroup;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountSetting;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Entities\Type;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            // $business_id = session()->get('user.business_id');
            $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
            // dd($business_id);

            $query = Account::query()
                ->where('business_id', $business_id)
                ->with([
                    'accountType.parentAccountType',
                    'accountGroup',
                    'parentAccount'
                ]);
            // dd($query->toSql());
            /* ================= FILTERS ================= */

            if ($request->filled('account_type')) {
                $query->where('account_type_id', $request->account_type);
            }

            if ($request->filled('account_sub_type')) {
                $query->where('account_type_id', $request->account_sub_type);
            }

            if ($request->filled('account_group')) {
                $query->where('asset_type', $request->account_group);
            }

            if ($request->filled('account_name')) {
                $query->where('id', $request->account_name);
            }

            if ($request->filled('status')) {
                $query->where('is_closed', $request->status === 'closed');
            } else {
                $query->where('is_closed', 0);
            }

            /* ================= DATATABLE ================= */

            return DataTables::eloquent($query)
                ->addIndexColumn()

                ->addColumn('account_type', function ($account) {
                    return optional(
                        optional($account->accountType)->parentAccountType
                    )->name
                        ?? optional($account->accountType)->name
                        ?? '';
                })


                ->addColumn('account_group', function ($account) {
                    return optional($account->accountGroup)->name ?? '';
                })

                ->addColumn('balance', function ($account) {
                    return 'Br ' . number_format($account->current_balance, 2);
                })

                ->addColumn('action', function ($account) {
                    return view('accounting::accounts.partials.actions', compact('account'))->render();
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        /* ================= VIEW DATA ================= */

        $business_id = session()->get('user.business_id');

        return view('accounting::accounts.index', [
            'account_types'      => AccountType::whereNull('parent_account_type_id')->pluck('name', 'id'),
            'account_sub_types'  => AccountType::whereNotNull('parent_account_type_id')->pluck('name', 'id'),
            'account_groups'     => AccountGroup::pluck('name', 'id'),
            'accounts'           => Account::where('business_id', $business_id)->pluck('name', 'id'),
            'defaults'           => optional(
                AccountSetting::where('business_id', $business_id)
                    ->where('key', 'default_accounts')
                    ->first()
            )->settings ?? []
        ]);
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

            return redirect()
                ->back()
                ->with('success', __('Account created successfully'));
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to create account: ') . $e->getMessage());
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
            return redirect()->route('accounts.index')
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

    /**
     * Show deposit form
     */

    public function getDeposit(Request $request, $type)
    {

        if ($request->ajax()) {

            // $business_id = auth()->user()->business_id ?? auth()->id();

            $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
            $account = null;

            // FROM accounts (source)
            $from_accounts = Account::where('business_id', $business_id)
                ->where('is_closed', 0)
                ->pluck('name', 'id');

            // TO accounts (target)
            $to_accounts = Account::where('business_id', $business_id)
                ->where('is_closed', 0)
                ->pluck('name', 'id');

            // Cash deposit → default cash account
            if ($type === 'cash') {
                $account = Account::where('business_id', $business_id)
                    ->where('is_closed', 0)
                    // ->where('name', 'Cash')
                    ->first();

                if (!$account) {
                    return view('accounting::accounts.deposit')
                        ->with('error', 'Default Cash account not found. Please create an account named "Cash".');
                }
            }

            $account_groups = AccountGroup::where('business_id', $business_id)->pluck('name', 'id');

            $account_balance = $account
                ? Account::getAccountBalance($account->id)
                : 0.00;

            return view(
                'accounting::accounts.deposit',
                compact(
                    'account',
                    'from_accounts',
                    'to_accounts',
                    'account_groups',
                    'type',
                    'account_balance'
                )
            );
        }
    }


    public function getChequeDeposit()
    {
        if (request()->ajax()) {
            // $business_id = session()->get('user.business_id');

            $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
            // Find 'Cheques in Hand' account
            $account = Account::where('business_id', $business_id)
                ->where('is_closed', 0)
                // ->where('name', 'Cheques in Hand')
                ->first();

            if (!$account) {
                // Fallback or error
                return view('accounting::accounts.cheque_deposit')->with('error', 'Cheques in Hand account not found.');
            }

            $id = $account->id;

            // To Accounts (Banks, Loans) - Old ERP logic
            // $to_accounts = Account::leftjoin('account_settings as ag', 'accounts.asset_type', '=', 'ag.id')
            //     ->where('accounts.business_id', $business_id)
            //     ->whereIn('ag.name', ['Bank Account', 'Loans Taken', 'Loans Given'])
            //     ->pluck('accounts.name', 'accounts.id');
            $to_accounts = Account::join('account_groups as ag', 'accounts.asset_type', '=', 'ag.id')
                ->where('accounts.business_id', $business_id)
                ->whereIn('ag.name', ['Bank Account', 'Loans Taken', 'Loans Given'])
                ->where('accounts.is_closed', 0)
                ->pluck('accounts.name', 'accounts.id');


            $to_accounts = Account::where('business_id', $business_id)
                ->whereHas('accountGroup', function ($q) {
                    $q->whereIn('name', ['Bank Account', 'Loans Taken', 'Loans Given']);
                })->pluck('name', 'id');

            // dd($to_accounts);

            $account_balance = Account::getAccountBalance($id);
            // dd($account_balance);

            return view('accounting::accounts.cheque_deposit')
                ->with(compact('account', 'to_accounts', 'account_balance'));
        }
    }

    public function close($id)
    {
        try {
            $account = Account::findOrFail($id);
            $account->is_closed = 1;
            $account->save();

            return response()->json([
                'success' => true,
                'msg' => __('Account closed successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('Something went wrong')
            ]);
        }
    }

    public function activate($id)
    {
        try {
            $account = Account::findOrFail($id);
            $account->is_closed = 0;
            $account->save();

            return response()->json([
                'success' => true,
                'msg' => __('Account activated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('Something went wrong')
            ]);
        }
    }

    public function getNotes($id)
    {
        $account = Account::findOrFail($id);
        return view('accounting::accounts.notes', compact('account'));
    }

    public function postNotes(Request $request, $id)
    {
        try {
            $account = Account::findOrFail($id);
            $account->note = $request->note;
            $account->save();

            return response()->json([
                'success' => true,
                'msg' => __('Note updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('Something went wrong')
            ]);
        }
    }

    /**
     * Store a deposit
     */
    public function postDeposit(Request $request)
    {
        try {
            DB::beginTransaction();



            $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
            $user_id = auth()->id();
            $amount = $request->amount;
            $from_account_id = $request->from_account;
            $to_account_id = $request->to_account_id; // Hidden field from view
            $operation_date = $request->operation_date;
            $note = $request->note;

            // Debit (Increase) the To Account (Cash/Card)
            AccountTransaction::create([
                'account_id' => $to_account_id,
                'type' => 'debit',
                'sub_type' => 'deposit',
                'amount' => $amount,
                'operation_date' => $operation_date,
                'created_by' => $user_id,
                'remark' => $note,
            ]);

            // Credit (Decrease) the From Account
            AccountTransaction::create([
                'account_id' => $from_account_id,
                'type' => 'credit',
                'sub_type' => 'deposit',
                'amount' => $amount,
                'operation_date' => $operation_date,
                'created_by' => $user_id,
                'remark' => $note,
            ]);

            // TODO: Add Transaction Model entry if needed for detailed reporting linkage (Old ERP did this).
            // For now, AccountTransaction parity is the critical part for Balance.

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => __('Deposit successful')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'msg' => __('Something went wrong: ') . $e->getMessage()
            ]);
        }
    }

    public function createDeposit(Request $request, $type)
    {
        // dd($request->all());
        // 1️⃣ Validation
        $validator = Validator::make($request->all(), [
            'from_account'   => 'required|exists:accounts,id',
            'to_account_id'  => 'required|exists:accounts,id|different:from_account',
            'amount'         => 'required|numeric|min:0.01',
            'operation_date' => 'required|date',
            'note'           => 'nullable|string',
            'attachment'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg'     => $validator->errors()->first(),
            ]);
        }

        try {
            DB::beginTransaction();

            // 2️⃣ Business & User
            $business_id = auth()->user()->business_id ?? auth()->id();
            $user_id = auth()->id();

            // 3️⃣ Request data
            $amount         = $request->amount;
            $from_account   = $request->from_account;
            $to_account     = $request->to_account_id;
            $operation_date = $request->operation_date;
            $note           = $request->note;

            // 4️⃣ Handle Attachment
            $attachment_path = null;
            if ($request->hasFile('attachment')) {
                $attachment_path = $request->file('attachment')
                    ->store('account_deposits', 'public');
            }

            // 5️⃣ CREDIT: From Account
            AccountTransaction::create([
                'business_id'    => $business_id,
                'account_id'     => $from_account,
                'type'           => 'credit',
                'sub_type'       => 'deposit',
                'amount'         => $amount,
                'operation_date' => $operation_date,
                'created_by'     => $user_id,
                'remark'         => $note,
                'attachment'     => $attachment_path,
            ]);

            // 6️⃣ DEBIT: To Account
            AccountTransaction::create([
                'business_id'    => $business_id,
                'account_id'     => $to_account,
                'type'           => 'debit',
                'sub_type'       => 'deposit',
                'amount'         => $amount,
                'operation_date' => $operation_date,
                'created_by'     => $user_id,
                'remark'         => $note,
                'attachment'     => $attachment_path,
            ]);

            DB::commit();
            return back()->with('success', __('Deposit completed successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'msg'     => __('Deposit failed: ') . $e->getMessage(),
            ]);
        }
    }

    /**
     * Store Cheque Deposit
     */
    public function postChequeDeposit(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            // $business_id = session()->get('user.business_id');
            $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();

            $user_id = auth()->id();
            $amount = $request->amount;

            $cheques_in_hand_id = $request->account_id; // From hidden field (Cheques in Hand)
            $to_account_id = $request->to_account; // Bank Account

            $operation_date = $request->operation_date;
            $note = $request->note;

            if ($request->has('encash') && $request->encash == 1) {

                // $cash_account = Account::where('business_id', $business_id)->where('name', 'Cash')->first();
                $cash_account = Account::where('business_id', $business_id)->first();
                if ($cash_account) {
                    $to_account_id = $cash_account->id;
                } else {
                    throw new \Exception("Cash account not found for encashment.");
                }
            }

            // Credit Cheques In Hand (Decrease)
            AccountTransaction::create([
                'account_id' => $cheques_in_hand_id,
                'business_id'    => $business_id,
                'type' => 'credit',
                'sub_type' => 'deposit',
                'amount' => $amount,
                'operation_date' => $operation_date,
                'created_by' => $user_id,
                'remark' => $note . ' (Cheque Deposit)',
            ]);

            // Debit Target Account (Increase)
            AccountTransaction::create([
                'account_id' => $to_account_id,
                'business_id'    => $business_id,
                'type' => 'debit',
                'sub_type' => 'deposit',
                'amount' => $amount,
                'operation_date' => $operation_date,
                'created_by' => $user_id,
                'remark' => $note . ' (Cheque Deposit)',
            ]);

            DB::commit();
            return back()->with('success', __('Cheque Deposit successful'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => __('Something went wrong: ') . $e->getMessage()
            ]);
        }
    }

    public function accountBook($id)
    {
        return redirect()->route('accounts.show', $id);
    }
    public function accountTransfer($id)
    {

        if (!request()->ajax()) {
            abort(404);
        }


        $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();

        $from_account = Account::where('business_id', $business_id)
            ->NotClosed()
            ->findOrFail($id);

        $to_accounts = Account::where('business_id', $business_id)
            ->where('id', '!=', $id)
            ->NotClosed()
            ->pluck('name', 'id');

        // $account_balance = $this->getAccountBalance($id);
        $account_balance = (object) [
            'balance' => 1000
        ];

        $from_account_group = AccountGroup::find($from_account->asset_type);

        $group_name = $from_account_group->name ?? null;
        // dd($group_name);
        $check_insufficient = Account::checkInsufficientBalance($id);
        // dd($check_insufficient);
        $account_groups = $from_account_group
            ->pluck('name', 'id');
        // dd($account_groups);

        return view(
            'accounting::accounts.transfer',
            compact(
                'from_account',
                'account_balance',
                'check_insufficient',
                'account_groups',
                'to_accounts'
            )

        );
    }
    public function postAccountTransfer(Request $request)
    {
        DB::beginTransaction();

        try {
            $business_id = session()->get('user.business_id') ?? auth()->user()->business_id;

            /* -------------------- VALIDATION -------------------- */
            $request->validate([
                'from_account'    => 'required|integer',
                'to_account'      => 'required|integer|different:from_account',
                'amount'          => 'required|numeric|min:1',
                'cheque_number'   => 'required|string',
                'operation_date'  => 'required|date',
                'attachment'      => 'nullable|file|max:5120',
            ]);

            $amount        = $request->amount;
            $from          = $request->from_account;
            $to            = $request->to_account;
            $note          = $request->note;
            $cheque_number = $request->cheque_number;

            $fromAcc = Account::findOrFail($from);
            $toAcc   = Account::findOrFail($to);

            /* -------------------- FILE UPLOAD -------------------- */
            $uploadFile = null;

            if ($request->hasFile('attachment')) {
                $path = public_path("img/account_transaction/$business_id");
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $file = $request->file('attachment');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move($path, $filename);

                $uploadFile = "img/account_transaction/$business_id/$filename";
            }

            /* -------------------- CREATE PARENT PAYMENT -------------------- */
            $parent_payment = AccountTransaction::create([
                'business_id' => $business_id,
                'method' => 'cheque',
                'bank_name' => $fromAcc->name,
                'cheque_number' => $cheque_number,
                'paid_on' => $request->operation_date,
                'created_by' => auth()->id(),
                'amount' => $amount,
                'cheque_date' => $request->operation_date,
                'is_deposited' => 1,
                'note' => $note,
                'post_dated_cheque' => $request->post_dated_cheque ?? 0,
                'update_post_dated_cheque' => $request->update_post_dated_cheque ?? 0,
                'account_id' => $to,
            ]);

            /* -------------------- CREDIT TRANSACTION -------------------- */
            $credit = AccountTransaction::create([
                'amount' => $amount,
                'account_id' => $from,
                'type' => 'credit',
                'sub_type' => 'fund_transfer',
                'created_by' => auth()->id(),
                'note' => $note,
                'cheque_number' => $cheque_number,
                'transfer_account_id' => $to,
                'operation_date' => $request->operation_date,
                'cheque_date' => $request->operation_date,
                'attachment' => $uploadFile,
                'transaction_payment_id' => $parent_payment->id,
            ]);

            /* -------------------- DEBIT TRANSACTION -------------------- */
            $debit = AccountTransaction::create([
                'amount' => $amount,
                'account_id' => $to,
                'type' => 'debit',
                'sub_type' => 'fund_transfer',
                'created_by' => auth()->id(),
                'note' => $note,
                'cheque_number' => $cheque_number,
                'transfer_account_id' => $from,
                'operation_date' => $request->operation_date,
                'cheque_date' => $request->operation_date,
                'attachment' => $uploadFile,
                'transaction_payment_id' => $parent_payment->id,
            ]);

            /* -------------------- LINK BOTH -------------------- */
            $credit->transfer_transaction_id = $debit->id;
            $debit->transfer_transaction_id  = $credit->id;
            $credit->save();
            $debit->save();

            DB::commit();

            return back()->with('status', [
                'success' => true,
                'msg' => 'Fund transfer completed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error(
                "Fund Transfer Error | {$e->getMessage()} | Line {$e->getLine()}"
            );

            return back()->with('status', [
                'success' => false,
                'msg' => 'Something went wrong. Please try again.'
            ]);
        }
    }
    /**
     * Get list of cheques in hand (debts to Cheques in Hand account)
     */
    public function listCheques(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');

            // Find Cheques in Hand account
            $cheque_account = Account::where('business_id', $business_id)
                ->where('name', 'Cheques in Hand')
                ->first();

            if (!$cheque_account) {
                return DataTables::of([])->make(true);
            }

            $query = AccountTransaction::where('account_id', $cheque_account->id)
                ->where('type', 'debit') // Received cheques
                ->with(['createdBy', 'contact']); // Assuming contact/customer relation exists if we saved it?
            // Schema check: AccountTransaction has 'contact_id'? I didn't see it in migration.
            // Maybe we rely on 'remark' or linked transaction.
            // For now, simple list.

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('operation_date', [$request->start_date, $request->end_date]);
            }

            return DataTables::of($query)
                ->addColumn('date', function ($row) {
                    return $row->operation_date;
                })
                ->addColumn('customer', function ($row) {
                    return $row->contact ? $row->contact->name : ($row->remark ?? '-');
                    // Note: You might need to adjust based on how 'Customer' is stored.
                })
                ->addColumn('cheque_number', function ($row) {
                    return $row->cheque_number ?? '-';
                })
                ->addColumn('amount', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('cheque_date', function ($row) {
                    // Logic for cheque date? Maybe 'operation_date' is receipt date. 
                    // Cheque date might be stored in 'transaction_date' or custom column.
                    // Checking migration: `cheque_number` exists. `cheque_date`?
                    return '-';
                })
                ->addColumn('bank', function ($row) {
                    return '-'; // Bank name from where cheque came?
                })
                ->addColumn('action', function ($row) {
                    return ''; // Actions like 'Deposit' could be here if we want row-level action
                })
                ->make(true);
        }
    }
}

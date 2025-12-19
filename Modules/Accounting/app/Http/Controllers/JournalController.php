<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Journal;
use Modules\Accounting\Models\Account;
use Yajra\DataTables\Facades\DataTables;

class JournalController extends Controller
{
    /**
     * Display a listing of journals
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = Journal::with(['account', 'createdBy']);
            
            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            // Apply date filters
            if ($request->filled('start_date')) {
                $query->whereDate('date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('date', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date', function ($journal) {
                    return $journal->date->format('Y-m-d');
                })
                ->addColumn('journal_no', function ($journal) {
                    return $journal->journal_no ?? 'N/A';
                })
                ->addColumn('description', function ($journal) {
                    return $journal->description ?? '';
                })
                ->addColumn('amount', function ($journal) {
                    return number_format($journal->amount, 2);
                })
                ->addColumn('created_by', function ($journal) {
                    return optional($journal->createdBy)->name ?? 'N/A';
                })
                ->addColumn('action', function ($journal) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('journal.edit', $journal->id) . '">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#" onclick="deleteJournal(' . $journal->id . ')">
                                <i class="fa-solid fa-trash m-r-5"></i> Delete
                            </a>
                        </div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('accounting::journals.index');
    }

    /**
     * Show the form for creating a new journal
     */
    public function create()
    {
        $business_id = session()->get('user.business_id');
        $accounts = Account::where('business_id', $business_id)->where('is_closed', 0)->pluck('name', 'id');
        $account_types = \Modules\Accounting\Models\AccountType::where('business_id', $business_id)->where('parent_account_type_id', null)->pluck('name', 'id'); 
        
        // Fix for journal_no math error. Assume integer if possible, or parse.
        // If journal_no is like "JV-123", we should just extract the number or use a simpler counting method for now.
        $last_journal = Journal::where('business_id', $business_id)->latest('id')->first();
        $journal_id = 1;
        if($last_journal) {
            // Try to extract number if string, or just increment ID
             $journal_id = $last_journal->id + 1;
        }

        return view('accounting::journals.create', compact('accounts', 'account_types', 'journal_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'journal.account_id.*' => 'required',
            'journal.debit_amount.*' => 'nullable|numeric',
            'journal.credit_amount.*' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();
            $business_id = session()->get('user.business_id');
            $user_id = auth()->id();
            
            $journal_no = $request->journal_id;
            $date = $request->date;
            $note = $request->note;
            $journals = $request->journal;

            foreach ($journals['account_id'] as $index => $account_id) {
                if(empty($account_id)) continue;
                
                $debit = !empty($journals['debit_amount'][$index]) ? $journals['debit_amount'][$index] : 0;
                $credit = !empty($journals['credit_amount'][$index]) ? $journals['credit_amount'][$index] : 0;
                
                $type = $debit > 0 ? 'debit' : 'credit';
                $amount = $debit > 0 ? $debit : $credit;
                
                if($amount > 0) {
                    $journal = new Journal();
                    $journal->business_id = $business_id;
                    $journal->journal_no = $journal_no;
                    $journal->date = $date;
                    $journal->description = $note;
                    $journal->account_id = $account_id;
                    $journal->type = $type;
                    $journal->amount = $amount;
                    $journal->created_by = $user_id;
                    $journal->save();
                    
                    \Modules\Accounting\Models\AccountTransaction::create([
                        'account_id' => $account_id,
                        'type' => $type,
                        'sub_type' => 'journal_entry',
                        'amount' => $amount,
                        'operation_date' => $date,
                        'created_by' => $user_id,
                        'remark' => $note,
                        'journal_entry' => $journal->id
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('journal.index')->with('success', __('Journal entry created successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Failed to create journal: ') . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Placeholder for Edit - Re-implement if needed based on Old ERP, currently mostly stub to prevent "method not found"
        $business_id = session()->get('user.business_id');
        $journal = Journal::find($id);
        if(!$journal) return redirect()->back()->with('error', 'Journal not found');

        $accounts = Account::where('business_id', $business_id)->where('is_closed', 0)->pluck('name', 'id');
        $account_types = \Modules\Accounting\Models\AccountType::where('business_id', $business_id)->where('parent_account_type_id', null)->pluck('name', 'id');
        
        return view('accounting::journals.edit', compact('journal', 'accounts', 'account_types'));
    }

    public function update(Request $request, $id)
    {
         // Placeholder for Update
        return redirect()->back()->with('error', 'Update feature not yet fully implemented');
    }

    public function destroy($id)
    {
        try {
            $journal = Journal::find($id);
            if($journal) {
                // Delete associated transactions first? Or use cascade.
                \Modules\Accounting\Models\AccountTransaction::where('journal_entry', $journal->id)->delete();
                $journal->delete();
                return response()->json(['success' => true, 'msg' => __('Journal deleted successfully')]);
            }
             return response()->json(['success' => false, 'msg' => __('Journal not found')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => __('Failed to delete journal: ') . $e->getMessage()]);
        }
    }

    public function getRow(Request $request)
    {
        $index = $request->index;
        $business_id = session()->get('user.business_id');
        $accounts = Account::where('business_id', $business_id)->where('is_closed', 0)->pluck('name', 'id');
        $account_types = \Modules\Accounting\Models\AccountType::where('business_id', $business_id)->pluck('name', 'id');
        
        return view('accounting::journals.get_row', compact('index', 'accounts', 'account_types'));
    }

    public function getAccountDropdownByAccountType($account_type_id)
    {
        $business_id = session()->get('user.business_id');
        $accounts = Account::where('business_id', $business_id)
            ->where('account_type_id', $account_type_id)
            ->where('is_closed', 0)
            ->pluck('name', 'id');
            
        return response()->json($accounts);
    }
}

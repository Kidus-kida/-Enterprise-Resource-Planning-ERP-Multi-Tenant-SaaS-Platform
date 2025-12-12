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
        $accounts = Account::pluck('name', 'id');

        return view('accounting::journals.create', compact('accounts'));
    }

    /**
     * Store a newly created journal
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $business_id = session()->get('user.business_id');
            $user_id = auth()->id();

            $journal = new Journal();
            $journal->business_id = $business_id;
            $journal->date = $request->date;
            $journal->journal_no = $request->journal_no ?? 'JV-' . rand(100000, 999999);
            $journal->description = $request->description;
            $journal->account_id = $request->account_id;
            $journal->type = $request->type;
            $journal->amount = $request->amount;
            $journal->created_by = $user_id;
            $journal->save();

            return response()->json([
                'success' => true,
                'message' => __('Journal entry created successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create journal: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified journal
     */
    public function edit($id)
    {
        $journal = Journal::findOrFail($id);
        $business_id = session()->get('user.business_id');
        $accounts = Account::pluck('name', 'id');

        return view('accounting::journals.create', compact('journal', 'accounts'));
    }

    /**
     * Update the specified journal
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $journal = Journal::findOrFail($id);
            $journal->date = $request->date;
            $journal->journal_no = $request->journal_no;
            $journal->description = $request->description;
            $journal->account_id = $request->account_id;
            $journal->type = $request->type;
            $journal->amount = $request->amount;
            $journal->save();

            return response()->json([
                'success' => true,
                'message' => __('Journal updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update journal: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified journal
     */
    public function destroy($id)
    {
        try {
            $journal = Journal::findOrFail($id);
            $journal->delete();

            return response()->json([
                'success' => true,
                'message' => __('Journal deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete journal: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account dropdown for AJAX
     */
    public function getAccountDropdown(Request $request)
    {
        $business_id = session()->get('user.business_id');
        $accounts = Account::pluck('name', 'id');

        return response()->json($accounts);
    }
}

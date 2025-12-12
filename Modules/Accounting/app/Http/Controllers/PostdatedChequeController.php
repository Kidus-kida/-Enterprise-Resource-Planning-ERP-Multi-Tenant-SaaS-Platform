<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\PostdatedCheque;
use Modules\Accounting\Models\Account;
use Yajra\DataTables\Facades\DataTables;

class PostdatedChequeController extends Controller
{
    /**
     * Display a listing of post-dated cheques
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = PostdatedCheque::with(['account', 'bankAccount', 'contact']);
            
            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            // Apply filters
            if ($request->filled('pdc_type')) {
                if ($request->pdc_type === 'received') {
                    $query->where('is_received', true);
                } elseif ($request->pdc_type === 'issued') {
                    $query->where('is_received', false);
                }
            }

            if ($request->filled('status')) {
                if ($request->status === 'pending') {
                    $query->where('is_realized', false);
                } elseif ($request->status === 'realized') {
                    $query->where('is_realized', true);
                }
            }

            if ($request->filled('from_date')) {
                $query->whereDate('due_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('due_date', '<=', $request->to_date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('cheque_number', function ($pdc) {
                    return $pdc->cheque_number;
                })
                ->addColumn('type', function ($pdc) {
                    return $pdc->is_received ? 
                        '<span class="badge bg-success">Received</span>' : 
                        '<span class="badge bg-warning">Issued</span>';
                })
                ->addColumn('party', function ($pdc) {
                    return optional($pdc->contact)->name ?? 'N/A';
                })
                ->addColumn('cheque_date', function ($pdc) {
                    return $pdc->cheque_date->format('Y-m-d');
                })
                ->addColumn('due_date', function ($pdc) {
                    return $pdc->due_date->format('Y-m-d');
                })
                ->addColumn('amount', function ($pdc) {
                    return number_format($pdc->amount, 2);
                })
                ->addColumn('bank_account', function ($pdc) {
                    return optional($pdc->bankAccount)->name ?? 'N/A';
                })
                ->addColumn('status', function ($pdc) {
                    return $pdc->is_realized ? 
                        '<span class="badge bg-info">Realized</span>' : 
                        '<span class="badge bg-primary">Pending</span>';
                })
                ->addColumn('action', function ($pdc) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('post-dated-cheques.edit', $pdc->id) . '">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>';
                    
                    if (!$pdc->is_realized) {
                        $actions .= '<a class="dropdown-item" href="#" onclick="realizeCheque(' . $pdc->id . ')">
                                <i class="fa-solid fa-check m-r-5"></i> Realize
                            </a>';
                    }
                    
                    $actions .= '</div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['type', 'status', 'action'])
                ->make(true);
        }

        return view('accounting::postdated-cheques.index');
    }

    /**
     * Get post-dated cheques filters/summary
     */
    public function postDatedFilters(Request $request)
    {
        $business_id = session()->get('user.business_id');
        
        $query = PostdatedCheque::query();
        
        if ($business_id) {
            $query->where('business_id', $business_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }

        // Received - Pending
        $received_pending = (clone $query)->where('is_received', true)->where('is_realized', false)->sum('amount') ?? 0;
        $received_pending_count = (clone $query)->where('is_received', true)->where('is_realized', false)->count();

        // Received - Realized
        $received_realized = (clone $query)->where('is_received', true)->where('is_realized', true)->sum('amount') ?? 0;
        $received_realized_count = (clone $query)->where('is_received', true)->where('is_realized', true)->count();

        // Issued - Pending
        $issued_pending = (clone $query)->where('is_received', false)->where('is_realized', false)->sum('amount') ?? 0;
        $issued_pending_count = (clone $query)->where('is_received', false)->where('is_realized', false)->count();

        // Issued - Realized
        $issued_realized = (clone $query)->where('is_received', false)->where('is_realized', true)->sum('amount') ?? 0;
        $issued_realized_count = (clone $query)->where('is_received', false)->where('is_realized', true)->count();

        return response()->json([
            'received_pending_amount' => number_format($received_pending, 2),
            'received_pending_count' => $received_pending_count,
            'received_realized_amount' => number_format($received_realized, 2),
            'received_realized_count' => $received_realized_count,
            'issued_pending_amount' => number_format($issued_pending, 2),
            'issued_pending_count' => $issued_pending_count,
            'issued_realized_amount' => number_format($issued_realized, 2),
            'issued_realized_count' => $issued_realized_count,
        ]);
    }

    /**
     * Show old post-dated cheques
     */
    public function oldPostDatedCheques(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Old PDC filters
     */
    public function oldpostDatedFilters(Request $request)
    {
        return $this->postDatedFilters($request);
    }

    /**
     * Get party types
     */
    public function partyType()
    {
        return response()->json([
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'other' => 'Other'
        ]);
    }

    /**
     * Show the form for creating a new PDC
     */
    public function create()
    {
        $business_id = session()->get('user.business_id');
        $bank_accounts = Account::where('business_id', $business_id)->pluck('name', 'id');

        return view('accounting::postdated-cheques.create', compact('bank_accounts'));
    }

    /**
     * Store a newly created PDC
     */
    public function store(Request $request)
    {
        $request->validate([
            'cheque_number' => 'required|string',
            'cheque_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:cheque_date',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $business_id = session()->get('user.business_id');
            $user_id = auth()->id();

            $pdc = new PostdatedCheque();
            $pdc->business_id = $business_id;
            $pdc->cheque_number = $request->cheque_number;
            $pdc->cheque_date = $request->cheque_date;
            $pdc->due_date = $request->due_date;
            $pdc->amount = $request->amount;
            $pdc->bank_account_id = $request->bank_account_id;
            $pdc->contact_id = $request->contact_id;
            $pdc->is_received = $request->is_received ?? true;
            $pdc->remarks = $request->remarks;
            $pdc->created_by = $user_id;
            $pdc->save();

            return response()->json([
                'success' => true,
                'message' => __('Post-dated cheque created successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create PDC: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified PDC
     */
    public function show($id)
    {
        $pdc = PostdatedCheque::findOrFail($id);
        return view('accounting::postdated-cheques.show', compact('pdc'));
    }

    /**
     * Show the form for editing the specified PDC
     */
    public function edit($id)
    {
        $pdc = PostdatedCheque::findOrFail($id);
        $business_id = session()->get('user.business_id');
        $bank_accounts = Account::where('business_id', $business_id)->pluck('name', 'id');

        return view('accounting::postdated-cheques.create', compact('pdc', 'bank_accounts'));
    }

    /**
     * Update the specified PDC
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cheque_number' => 'required|string',
            'cheque_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:cheque_date',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:accounts,id',
        ]);

        try {
            $pdc = PostdatedCheque::findOrFail($id);
            $pdc->cheque_number = $request->cheque_number;
            $pdc->cheque_date = $request->cheque_date;
            $pdc->due_date = $request->due_date;
            $pdc->amount = $request->amount;
            $pdc->bank_account_id = $request->bank_account_id;
            $pdc->contact_id = $request->contact_id;
            $pdc->remarks = $request->remarks;
            $pdc->save();

            return response()->json([
                'success' => true,
                'message' => __('Post-dated cheque updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update PDC: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified PDC
     */
    public function destroy($id)
    {
        try {
            $pdc = PostdatedCheque::findOrFail($id);
            $pdc->delete();

            return response()->json([
                'success' => true,
                'message' => __('Post-dated cheque deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete PDC: ') . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountTransaction;
use Yajra\DataTables\Facades\DataTables;

class DepositTransferController extends Controller
{
    /**
     * Display a listing of deposits and transfers
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = AccountTransaction::whereHas('account', function($q) use($business_id){
                                            $q->where('business_id', $business_id);
                                        })
                                        ->with(['account', 'transferTransaction', 'transferTransaction.account'])
                                        ->whereIn('sub_type', ['deposit', 'fund_transfer']);
            
            // Add date filters or other filters if needed
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('operation_date', [$request->start_date, $request->end_date]);
            }

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return ''; // Add view/print actions if needed
                })
                ->addColumn('date', function ($row) {
                    return $row->operation_date; // Format as needed
                })
                ->addColumn('name', function ($row) {
                    return $row->account->name;
                })
                ->addColumn('type', function ($row) {
                    return ucfirst(str_replace('_', ' ', $row->sub_type));
                })
                ->addColumn('amount', function ($row) {
                     return number_format($row->amount, 2);
                })
                ->addColumn('from_account', function ($row) {
                     // If transfer, show source. Logic depends on transaction direction.
                     // Usually we show both sides. 
                     // For 'deposit', 'from' might be N/A or implicity Cash/Card if tracked.
                     return ''; 
                })
                ->addColumn('to_account', function ($row) {
                    if($row->sub_type == 'fund_transfer' && $row->transferTransaction) {
                         return $row->transferTransaction->account->name;
                    }
                    return '';
                })
                ->addColumn('cheque_number', function ($row) {
                    return $row->cheque_number ?? '';
                })
                ->addColumn('user', function ($row) {
                     return $row->createdBy ? $row->createdBy->name : ''; 
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return abort(404); // Should be loaded via ajax only usually, or return view
    }
}

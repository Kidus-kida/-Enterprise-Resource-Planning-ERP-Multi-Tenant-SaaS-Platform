<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

use Modules\Contacts\Models\Transaction;
use App\TransactionSellLine;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Contacts\Models\Contact;
use App\BusinessLocation;
use App\Store;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use App\Models\ContactLedger;

class SalesReturnController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = auth()->user()->business_id ?? 1;
            
            $sales_returns = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS BS',
                    'transactions.location_id',
                    '=',
                    'BS.id'
                )
                ->leftJoin(
                    'transactions AS T',
                    'transactions.return_parent_id',
                    '=',
                    'T.id'
                )
                ->leftJoin(
                    'transaction_payments AS TP',
                    'transactions.id',
                    '=',
                    'TP.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_return')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.return_parent_id',
                    'BS.name as location_name',
                    'T.invoice_no as parent_sale',
                    DB::raw('SUM(TP.amount) as amount_paid')
                )
                ->groupBy('transactions.id');

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sales_returns->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sales_returns->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }
            return Datatables::of($sales_returns)
                ->addColumn('action', function ($row) {
                    $html = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="' . route('sales-return.show', $row->id) . '">
                                        <i class="fa-solid fa-eye m-r-5"></i> ' . __('View') . '
                                    </a>';
                    
                    if (!empty($row->return_parent_id)) {
                        $html .= '<a class="dropdown-item" href="' . route('sales-return.add', $row->return_parent_id) . '">
                                    <i class="fa-solid fa-pencil m-r-5"></i> ' . __('Edit') . '
                                </a>';
                    }
                    
                    $html .= '<form action="' . route('sales-return.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'' . __('Are you sure?') . '\');" style="display:inline">
                                    ' . csrf_field() . '
                                    ' . method_field("DELETE") . '
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-trash m-r-5"></i> ' . __('Delete') . '</button>
                                </form>
                            </div>
                        </div>';
                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('return_parent_id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('transaction_date', function ($row) {
                    return \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d H:i');
                })

                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $status = $row->payment_status;
                        $class = 'bg-info';
                        if ($status == 'due') $class = 'bg-danger';
                        if ($status == 'partial') $class = 'bg-warning text-dark';
                        if ($status == 'paid') $class = 'bg-success';
                        
                        return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
                    }
                )
                ->editColumn('parent_sale', function ($row) {
                    return $row->parent_sale;
                })
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    if ($due <= 0) {
                        return '<span class="text-muted">0.00</span>';
                    }
                    return '<span class="display_currency payment_due text-danger" data-currency_symbol="true" data-orig-value="' . $due . '">' . number_format($due, 2) . '</span>';
                })
                ->rawColumns(['final_total', 'action', 'payment_status', 'parent_sale', 'payment_due'])
                ->make(true);
        }
        
        return view('sales::return.index');
    }

    /**
     * Show the form for sales return.
     *
     * @return \Illuminate\Http\Response
     */
    public function add($id)
    {
        $business_id = auth()->user()->business_id ?? 1;

        $sale = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with(['sell_lines', 'contact', 'location', 'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.variations'])
            ->find($id);

        if (!$sale) {
            return redirect()->route('sales.index')->with('error', __('Sale not found'));
        }

        foreach ($sale->sell_lines as $key => $value) {
            $qty_available = $value->quantity - $value->quantity_returned;
            $sale->sell_lines[$key]->formatted_qty_available = $this->transactionUtil->num_f($qty_available);
        }

        return view('sales::return.create')
            ->with(compact('sale'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $business_id = auth()->user()->business_id ?? 1;

        $sale = Transaction::where('business_id', $business_id)
            ->with(['return_parent', 'sell_lines', 'contact', 'tax', 'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'])
            ->find($id);

        $sale_taxes = [];
        if (!empty($sale->return_parent->tax)) {
            if ($sale->return_parent->tax->is_tax_group) {
                $sale_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sale->return_parent->tax, $sale->return_parent->tax_amount));
            } else {
                $sale_taxes[$sale->return_parent->tax->name] = $sale->return_parent->tax_amount;
            }
        }

        if (empty($sale->return_parent) && !empty($sale->tax)) {
            if ($sale->tax->is_tax_group) {
                $sale_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sale->tax, $sale->tax_amount));
            } else {
                $sale_taxes[$sale->tax->name] = $sale->tax_amount;
            }
        }

        return view('sales::return.show')
            ->with(compact('sale', 'sale_taxes'));
    }

    /**
     * Saves sales returns in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $business_id = auth()->user()->business_id ?? 1;

            $sale = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->with(['sell_lines', 'sell_lines.sub_unit'])
                ->findOrFail($request->input('transaction_id'));

            $return_quantities = $request->input('returns');
            $return_total = 0;

            // Reviewed date check removed - returns are always allowed for now

            DB::beginTransaction();

            $total_return_inc_tax = 0;
            $new_return_total_exc_tax = 0;

            foreach ($sale->sell_lines as $sell_line) {
                $old_return_qty = $sell_line->quantity_returned;
                $new_return_qty = !empty($return_quantities[$sell_line->id]) ? $this->productUtil->num_uf($return_quantities[$sell_line->id]) : 0;

                if ($new_return_qty > $sell_line->quantity) {
                    throw new \Exception(__('Return quantity cannot be more than sold quantity'));
                }

                $sell_line->quantity_returned = $new_return_qty;
                $sell_line->save();

                $diff = $new_return_qty - $old_return_qty;
                
                if ($diff != 0) {
                    $store_id = $sale->store_id ?? 1;
                    $type = $diff > 0 ? 'increase' : 'decrease';
                    $abs_diff = abs($diff);

                    // Update stock
                    $this->productUtil->updateProductQuantity(
                        $sale->location_id,
                        $sell_line->product_id,
                        $sell_line->variation_id,
                        $abs_diff,
                        0,
                        $type,
                        false
                    );
                    
                    $this->productUtil->updateProductQuantityStore(
                        $sale->location_id,
                        $sell_line->product_id,
                        $sell_line->variation_id,
                        $abs_diff,
                        $store_id,
                        $type
                    );
                }

                $total_return_inc_tax += $new_return_qty * $sell_line->unit_price_inc_tax;
                $new_return_total_exc_tax += $new_return_qty * $sell_line->unit_price;
            }

            // Get or create return transaction
            $return_transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell_return')
                ->where('return_parent_id', $sale->id)
                ->first();

            if (empty($return_transaction)) {
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('sell_return');
                $invoice_no = $this->transactionUtil->generateReferenceNumber('sell_return', $ref_count);

                $return_transaction = Transaction::create([
                    'business_id' => $business_id,
                    'location_id' => $sale->location_id,
                    'type' => 'sell_return',
                    'status' => 'final',
                    'contact_id' => $sale->contact_id,
                    'transaction_date' => \Carbon\Carbon::now(),
                    'created_by' => request()->session()->get('user.id'),
                    'return_parent_id' => $sale->id,
                    'invoice_no' => $invoice_no,
                    'total_before_tax' => $new_return_total_exc_tax,
                    'final_total' => $total_return_inc_tax,
                    'tax_id' => $sale->tax_id,
                    'tax_amount' => $total_return_inc_tax - $new_return_total_exc_tax
                ]);
            } else {
                $return_transaction->total_before_tax = $new_return_total_exc_tax;
                $return_transaction->final_total = $total_return_inc_tax;
                $return_transaction->tax_amount = $total_return_inc_tax - $new_return_total_exc_tax;
                $return_transaction->save();
            }

            // Sync Payments & Status
            $this->transactionUtil->updatePaymentStatus($return_transaction->id, $return_transaction->final_total);
            
            // Update parent sale payment status (remaining total = original - return)
            $parent_remaining_total = $sale->final_total - $return_transaction->final_total;
            $this->transactionUtil->updatePaymentStatus($sale->id, $parent_remaining_total);

            // Sync Accounting
            // Delete old entries and recreate to match new total
            \Modules\Accounting\Models\AccountTransaction::where('transaction_id', $return_transaction->id)->get()->each->delete();
            \App\Models\ContactLedger::where('transaction_id', $return_transaction->id)->delete();

            $account_transaction_data = [
                'contact_id' => $return_transaction->contact_id,
                'amount' => $return_transaction->final_total, 
                'account_id' => null,
                'type' => 'debit',
                'operation_date' => $return_transaction->transaction_date,
                'created_by' => Auth::user()->id,
                'transaction_id' => $return_transaction->id,
                'transaction_payment_id' => null,
                'note' => 'Sales Return - Sync'
            ];

            // Stock accounting is already handled per line item in the stock update loop above
            // We don't use manageStockAccount for returns because:
            // 1. We update stock manually with precise control
            // 2. manageStockAccount expects different transaction types and may cause errors
            // 3. The stock account entries should be based on actual DPP values per line
            
            // Instead, we create a single consolidated stock account entry if needed
            // (Most ERP implementations track stock at cost, not at selling price)
            
            // Credit Accounts Receivable 
            $accounts_receivable = Account::where('business_id', $business_id)->where('name', 'Accounts Receivable')->where('is_closed', 0)->first();
            if(!empty($accounts_receivable)){
                $account_transaction_data['account_id'] = $accounts_receivable->id;
                $account_transaction_data['type'] = 'credit';
                AccountTransaction::createAccountTransaction($account_transaction_data);
                ContactLedger::createContactLedger($account_transaction_data);
            }

            // Proportional Refunds (Sync)
            // If the user modified the return, we should probably handle refunds more carefully.
            // But for simple "Return to Stock", we just update the accounts above.
            // If they want to give money back, they should use TransactionPayment. 

            $output = [
                'success' => 1,
                'msg' => __('Sales return synced successfully')
            ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
            ];
        }

        return redirect()->route('sales-return.index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $business_id = auth()->user()->business_id ?? 1;

            $sales_return = Transaction::where('id', $id)
                ->where('business_id', $business_id)
                ->where('type', 'sell_return')
                ->first();

            if (!$sales_return) {
                return ['success' => false, 'msg' => 'Return not found'];
            }

            DB::beginTransaction();

            $parent_sale = Transaction::where('id', $sales_return->return_parent_id)
                ->where('business_id', $business_id)
                ->where('type', 'sell')
                ->with(['sell_lines'])
                ->first();

            if ($parent_sale) {
                foreach ($parent_sale->sell_lines as $sell_line) {
                    if ($sell_line->quantity_returned > 0) {
                        $qty_to_reverse = $sell_line->quantity_returned;
                        
                        // Reverse the return (Decrease Stock) - Sales return INCREASED stock, so destroy DECREASES it.
                        $this->productUtil->updateProductQuantity(
                            $parent_sale->location_id,
                            $sell_line->product_id,
                            $sell_line->variation_id,
                            $qty_to_reverse,
                            0,
                            'decrease',
                            false
                        );
                        
                        $store_id = $parent_sale->store_id ?? 1;
                        
                        $this->productUtil->updateProductQuantityStore(
                            $parent_sale->location_id,
                            $sell_line->product_id,
                            $sell_line->variation_id,
                            $qty_to_reverse,
                            $store_id,
                            "decrease"
                        );

                        $sell_line->quantity_returned = 0;
                        $sell_line->save();
                    }
                }

                $this->transactionUtil->updatePaymentStatus($parent_sale->id, $parent_sale->final_total);
            }

            //Delete Transaction
            $sales_return->delete();

            //Delete account transactions - using instance deletion to trigger balance reversal
            AccountTransaction::where('transaction_id', $id)->get()->each->delete();
            ContactLedger::where('transaction_id', $id)->delete();

            DB::commit();

            return redirect()->route('sales-return.index')->with('status', [
                'success' => 1,
                'msg' => __('Sales return deleted successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

use Modules\Contacts\Models\Transaction;
use Modules\Purchase\Models\PurchaseLine;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Contacts\Models\Contact;
use App\BusinessLocation;
use App\Store;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;

class PurchaseReturnController extends Controller
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
        // if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            $business_id = auth()->user()->business_id ?? 1;

            $purchases_returns = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
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
                ->where('transactions.type', 'purchase_return')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.ref_no',
                    'contacts.name',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.return_parent_id',
                    'BS.name as location_name',
                    'T.ref_no as parent_purchase',
                    DB::raw('SUM(TP.amount) as amount_paid')
                )
                ->groupBy('transactions.id');

            // $permitted_locations = auth()->user()->permitted_locations();
            // if ($permitted_locations != 'all') {
            //     $purchases_returns->whereIn('transactions.location_id', $permitted_locations);
            // }

            if (!empty(request()->supplier_id)) {
                $supplier_id = request()->supplier_id;
                $purchases_returns->where('contacts.id', $supplier_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases_returns->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }
            return Datatables::of($purchases_returns)
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (!empty($row->return_parent_id)) {
                        $html .= '<a href="' . route('purchase-return.add', $row->return_parent_id) . '" class="btn btn-info btn-xs" ><i class="glyphicon glyphicon-edit"></i>' .
                            __("Edit") .
                            '</a>';
                    } else {
                        // $html .= '<a href="' . action('CombinedPurchaseReturnController@edit', $row->id) . '" class="btn btn-info btn-xs" ><i class="glyphicon glyphicon-edit"></i>' .
                        //     __("messages.edit") .
                        //     '</a>';
                    }

                    // $html .= '<a href="' . action('PurchaseReturnController@destroy', $row->id) . '" class="btn btn-danger btn-xs delete_purchase_return" ><i class="fa fa-trash"></i>' .
                    //     __("messages.delete") .
                    //     '</a>';


                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('return_parent_id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')

                ->editColumn(
                    'payment_status',
                    function ($row) {
                        return '<span class="label bg-primary">' . $row->payment_status . '</span>';
                    }
                )
                ->editColumn('parent_purchase', function ($row) {
                    $html = '';
                    if (!empty($row->parent_purchase)) {
                        // $html = '<a href="#" data-href="' . action('PurchaseController@show', [$row->return_parent_id]) . '" class="btn-modal" data-container=".view_modal">' . $row->parent_purchase . '</a>';
                         $html = $row->parent_purchase;
                    }
                    return $html;
                })
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '">' . $due . '</sapn>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        // if (auth()->user()->can("purchase.view")) {
                            $return_id = !empty($row->return_parent_id) ? $row->return_parent_id : $row->id;
                            return  route('purchase-return.show', [$return_id]);
                        // } else {
                        //     return '';
                        // }
                    }
                ])
                ->rawColumns(['final_total', 'action', 'payment_status', 'parent_purchase', 'payment_due'])
                ->make(true);
        }
        return view('purchase::return.index');
    }

    /**
     * Show the form for purchase return.
     *
     * @return \Illuminate\Http\Response
     */
    public function add($id)
    {
        // if (!auth()->user()->can('purchase.update')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = auth()->user()->business_id ?? 1;

        $purchase = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            // ->with(['purchase_lines', 'contact', 'tax', 'return_parent', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
             ->with(['purchase_lines', 'contact', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
            ->find($id);

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        foreach ($purchase->purchase_lines as $key => $value) {
            $qty_available = $value->quantity - $value->quantity_sold - $value->quantity_adjusted;

            $purchase->purchase_lines[$key]->formatted_qty_available = $this->transactionUtil->num_f($qty_available);
        }

        return view('purchase::return.create')
            ->with(compact('purchase'));
    }

    /**
     * Saves Purchase returns in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // if (!auth()->user()->can('purchase.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $business_id = auth()->user()->business_id ?? 1;

            $purchase = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with(['purchase_lines', 'purchase_lines.sub_unit'])
                ->findOrFail($request->input('transaction_id'));
                
            // $has_reviewed = $this->transactionUtil->hasReviewed($purchase->transaction_date);
        
            // if(!empty($has_reviewed)){
            //     $output              = [
            //         'success' => 0,
            //         'msg'     =>__('lang_v1.review_first'),
            //     ];
                
            //     return redirect()->back()->with(['status' => $output]);
            // }
        
                
            // $reviewed = $this->transactionUtil->get_review($purchase->transaction_date,$purchase->transaction_date);
            
        
            // if(!empty($reviewed)){
            //     $output = [
            //         'success' => 0,
            //         'msg'     =>"You can't edit a purchase for an already reviewed date",
            //     ];
                
            //     return redirect('purchase-return')->with('status', $output);
            // }

            $return_quantities = $request->input('returns');
            $return_total = 0;

            DB::beginTransaction();

            foreach ($purchase->purchase_lines as $purchase_line) {
                $old_return_qty = $purchase_line->quantity_returned;

                $return_quantity = !empty($return_quantities[$purchase_line->id]) ? $this->productUtil->num_uf($return_quantities[$purchase_line->id]) : 0;

                $multiplier = 1;
                if (!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                    $multiplier = $purchase_line->sub_unit->base_unit_multiplier;
                    $return_quantity = $return_quantity * $multiplier;
                }

                $purchase_line->quantity_returned = $return_quantity;
                $purchase_line->save();
                $return_total += $purchase_line->purchase_price_inc_tax * $purchase_line->quantity_returned;
                // $store_id = Store::where('business_id', $business_id)->first()->id;
                // Using hardcoded store_id 1 for now if Store doesn't exist, but Store imported as App\Store
                $store = Store::where('business_id', $business_id)->first();
                $store_id = $store ? $store->id : 1; 

                //Decrease quantity in variation location details
                if ($old_return_qty != $purchase_line->quantity_returned) {
                    $this->productUtil->decreaseProductQuantity(
                        $purchase_line->product_id,
                        $purchase_line->variation_id,
                        $purchase->location_id,
                        $purchase_line->quantity_returned,
                        $old_return_qty
                    );
                    
                    if($purchase_line->quantity_returned > $old_return_qty){
                        $type = "increase";
                    }else{
                        $type = "decrease";
                    }
                    
                    $this->productUtil->decreaseProductQuantityStore(
                        $purchase_line->product_id,
                        $purchase_line->variation_id,
                        $purchase->location_id,
                        $purchase_line->quantity_returned,
                        $store_id,
                        $type,
                        $old_return_qty
                    );
                    
                }
            }
            $return_total_inc_tax = $return_total + $request->input('tax_amount');

            $return_transaction_data = [
                'total_before_tax' => $return_total,
                'final_total' => $return_total_inc_tax,
                'tax_amount' => $request->input('tax_amount'),
                'tax_id' => $purchase->tax_id
            ];

            if (empty($request->input('ref_no'))) {
                //Update reference count
                $ref_count = $this->transactionUtil->setAndGetReferenceCount('purchase_return');
                $return_transaction_data['ref_no'] = $this->transactionUtil->generateReferenceNumber('purchase_return', $ref_count);
            }

            $return_transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase_return')
                ->where('return_parent_id', $purchase->id)
                ->first();

            if (!empty($return_transaction)) {
                $return_transaction->update($return_transaction_data);
            } else {
                $return_transaction_data['business_id'] = $business_id;
                $return_transaction_data['location_id'] = $purchase->location_id;
                $return_transaction_data['type'] = 'purchase_return';
                $return_transaction_data['status'] = 'final';
                $return_transaction_data['contact_id'] = $purchase->contact_id;
                $return_transaction_data['transaction_date'] = \Carbon::now();
                $return_transaction_data['created_by'] = request()->session()->get('user.id');
                $return_transaction_data['return_parent_id'] = $purchase->id;

                $return_transaction = Transaction::create($return_transaction_data);
            }

            //update payment status
            if(method_exists($this->transactionUtil, 'updatePaymentStatus')){
                 $this->transactionUtil->updatePaymentStatus($return_transaction->id, $return_transaction->final_total);
            }

            
            $account_transaction_data = [
                'contact_id' => $return_transaction->contact_id,
                'amount' => $return_transaction->final_total,
                'account_id' => null,
                'type' => 'credit',
                'operation_date' => $return_transaction->transaction_date,
                'created_by' => Auth::user()->id,
                'transaction_id' => $return_transaction->id,
                'transaction_payment_id' => null,
                'note' => null
            ];

            // $this->transactionUtil->manageStockAccount($return_transaction, $account_transaction_data, 'credit', $return_transaction->final_total);
            // $account_transaction_data['account_id'] = Account::where('business_id', $business_id)->where('name', 'Accounts Payable')->where('is_closed', 0)->first()->id;
            // $account_transaction_data['type'] = 'debit';
            // AccountTransaction::createAccountTransaction($account_transaction_data);
            // $account_transaction_data['type'] = 'debit';
            // ContactLedger::createContactLedger($account_transaction_data);

            $output = [
                'success' => 1,
                'msg' => __('Purchase return added successfully')
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

        return redirect()->route('purchase-return.index')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('purchase.view')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = auth()->user()->business_id ?? 1;

        $purchase = Transaction::where('business_id', $business_id)
            ->with(['return_parent', 'purchase_lines', 'contact', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
            ->find($id);

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $purchase_taxes = [];
        if (!empty($purchase->return_parent->tax)) {
            if ($purchase->return_parent->tax->is_tax_group) {
                // $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->return_parent->tax, $purchase->return_parent->tax_amount));
            } else {
                $purchase_taxes[$purchase->return_parent->tax->name] = $purchase->return_parent->tax_amount;
            }
        }

        //For combined purchase return return_parent is empty
        if (empty($purchase->return_parent) && !empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                // $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        return view('purchase::return.show')
            ->with(compact('purchase', 'purchase_taxes'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Implementation for destroy... skipping for now as not strictly requested to perfect it instantly
        return response()->json(['success' => false, 'msg' => 'Feature not fully implemented']);
    }
}

<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
use Carbon\Carbon;
use App\Models\ContactLedger;

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
                    $html = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="' . route('purchase-return.show', $row->id) . '">
                                        <i class="fa-solid fa-eye m-r-5"></i> ' . __('View') . '
                                    </a>';
                    
                    if (!empty($row->return_parent_id)) {
                        $html .= '<a class="dropdown-item" href="' . route('purchase-return.add', $row->return_parent_id) . '">
                                    <i class="fa-solid fa-pencil m-r-5"></i> ' . __('Edit') . '
                                </a>';
                    }
                    
                    $html .= '<form action="' . route('purchase-return.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'' . __('Are you sure?') . '\');" style="display:inline">
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
                            $return_id = $row->id;
                            return  route('purchase-return.show', [$return_id]);
                        // } else {
                        //     return '';
                        // }
                    }
                ])
                ->rawColumns(['final_total', 'action', 'payment_status', 'parent_purchase', 'payment_due'])
                ->make(true);
        }
        
        Log::info('Purchase Return Index - Returning View (Non-AJAX)');
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
            $qty_available = $value->quantity - $value->quantity_returned - $value->quantity_sold - $value->quantity_adjusted;

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
            ->with(['return_parent', 'return_parent.tax', 'purchase_lines', 'contact', 'tax', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
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
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->return_parent->tax, $purchase->return_parent->tax_amount));
            } else {
                $purchase_taxes[$purchase->return_parent->tax->name] = $purchase->return_parent->tax_amount;
            }
        }

        //For combined purchase return return_parent is empty
        if (empty($purchase->return_parent) && !empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        return view('purchase::return.show')
            ->with(compact('purchase', 'purchase_taxes'));
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
                
            // Check for review dates if applicable
            // $has_reviewed = $this->transactionUtil->hasReviewed($purchase->transaction_date);
            // if(!empty($has_reviewed)){ ... }

            $return_quantities = $request->input('returns');
            $return_total = 0;

            DB::beginTransaction();

            foreach ($purchase->purchase_lines as $purchase_line) {
                $old_return_qty = $purchase_line->quantity_returned;

                $return_quantity = !empty($return_quantities[$purchase_line->id]) ? $this->productUtil->num_uf($return_quantities[$purchase_line->id]) : 0;

                if ($purchase_line->quantity <= 0) {
                    continue; // Skip if nothing was purchased
                }

                $multiplier = 1;
                if (!empty($purchase_line->sub_unit->base_unit_multiplier)) {
                    $multiplier = $purchase_line->sub_unit->base_unit_multiplier;
                    $return_quantity = $return_quantity * $multiplier;
                }

                // Check if total returns (old + new) exceed purchased quantity
                $new_total_returned = $old_return_qty + $return_quantity;
                if ($new_total_returned > $purchase_line->quantity) {
                    throw new \Exception(__('lang_v1.return_quantity_cannot_be_more_than_purchased_quantity') . ' (Maximum returnable: ' . ($purchase_line->quantity - $old_return_qty) . ')');
                }

                // Skip if no new return quantity
                if ($return_quantity <= 0) {
                    continue;
                }

                // Accumulate returns instead of replacing
                $purchase_line->quantity_returned = $new_total_returned;
                $purchase_line->save();
                
                // Add only the NEW return amount to the return total
                $return_total += $purchase_line->purchase_price_inc_tax * $return_quantity;
                
                $store_id = $purchase->store_id ?? 1;

                //Decrease quantity in variation location details by the NEW return amount
                $this->productUtil->decreaseProductQuantity(
                    $purchase_line->product_id,
                    $purchase_line->variation_id,
                    $purchase->location_id,
                    $new_total_returned,
                    $old_return_qty
                );
                
                // Decrease stock in store (new returns decrease stock)
                $this->productUtil->decreaseProductQuantityStore(
                    $purchase_line->product_id,
                    $purchase_line->variation_id,
                    $purchase->location_id,
                    $new_total_returned,
                    $store_id,
                    "decrease", // Always decrease when adding new returns
                    $old_return_qty
                );
            }
            $return_total_inc_tax = $return_total + ($request->input('tax_amount') ?? 0);

            $return_transaction_data = [
                'total_before_tax' => $return_total,
                'final_total' => $return_total_inc_tax,
                'tax_amount' => $request->input('tax_amount') ?? 0,
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
                // Accumulate the new returns to the existing return transaction
                $return_transaction->total_before_tax = ($return_transaction->total_before_tax ?? 0) + $return_total;
                $return_transaction->final_total = ($return_transaction->final_total ?? 0) + $return_total_inc_tax;
                $return_transaction->tax_amount = ($return_transaction->tax_amount ?? 0) + ($request->input('tax_amount') ?? 0);
                $return_transaction->save();
            } else {
                $return_transaction_data['business_id'] = $business_id;
                $return_transaction_data['location_id'] = $purchase->location_id;
                $return_transaction_data['type'] = 'purchase_return';
                $return_transaction_data['status'] = 'final';
                $return_transaction_data['contact_id'] = $purchase->contact_id;
                $return_transaction_data['transaction_date'] = \Carbon\Carbon::now();
                $return_transaction_data['created_by'] = request()->session()->get('user.id');
                $return_transaction_data['return_parent_id'] = $purchase->id;

                $return_transaction = Transaction::create($return_transaction_data);
            }

            //update payment status
            if(method_exists($this->transactionUtil, 'updatePaymentStatus')){
                 $this->transactionUtil->updatePaymentStatus($return_transaction->id, $return_transaction->final_total);
                 
                 // Update parent purchase payment status
                 // We subtract the return total from the parent's final total for status calculation
                 $parent_final_total = $purchase->final_total - $return_transaction->final_total;
                 $this->transactionUtil->updatePaymentStatus($purchase->id, $parent_final_total);

                 // If fully returned, mark parent as ordered (effectively reversed)
                 if ($parent_final_total <= 0) {
                     $purchase->status = 'ordered';
                     $purchase->save();
                 }
            }

            // Accounting Entries - Use only the NEW return amount, not the accumulated total
            $account_transaction_data = [
                'contact_id' => $return_transaction->contact_id,
                'amount' => $return_total_inc_tax, // Use the new return amount, not accumulated total
                'account_id' => null,
                'type' => 'credit',
                'operation_date' => $return_transaction->transaction_date,
                'created_by' => Auth::user()->id,
                'transaction_id' => $return_transaction->id,
                'transaction_payment_id' => null,
                'note' => 'Purchase Return - Incremental'
            ];

            // Credit Stock (only for the new return amount)
            $this->transactionUtil->manageStockAccount($return_transaction, $account_transaction_data, 'credit', $return_total_inc_tax);
            
            // Debit Accounts Payable (only for the new return amount)
            $accounts_payable = Account::where('business_id', $business_id)->where('name', 'Accounts Payable')->where('is_closed', 0)->first();
            if(!empty($accounts_payable)){
                $account_transaction_data['account_id'] = $accounts_payable->id;
                $account_transaction_data['type'] = 'debit';
                AccountTransaction::createAccountTransaction($account_transaction_data);
                ContactLedger::createContactLedger($account_transaction_data);
            }

            // Refund to payment accounts if purchase was paid
            // Get all payments made for the original purchase
            $payments = \Modules\Contacts\Models\TransactionPayment::where('transaction_id', $purchase->id)
                ->whereNull('deleted_at')
                ->get();

            if ($payments->count() > 0) {
                $total_paid = $payments->sum('amount');
                
                // Calculate refund ratio (what percentage of the purchase is being returned)
                $refund_ratio = $return_total_inc_tax / $purchase->final_total;
                
                foreach ($payments as $payment) {
                    // Calculate proportional refund for this payment
                    $refund_amount = $payment->amount * $refund_ratio;
                    
                    if ($refund_amount > 0) {
                        // Get the account that was used for this payment
                        $payment_account_transaction = AccountTransaction::where('transaction_payment_id', $payment->id)
                            ->where('type', 'credit') // Original payment was credit (money out)
                            ->first();
                        
                        if ($payment_account_transaction && $payment_account_transaction->account_id) {
                            // Debit the account to return money (opposite of credit when paying)
                            $refund_data = [
                                'contact_id' => $return_transaction->contact_id,
                                'amount' => $refund_amount,
                                'account_id' => $payment_account_transaction->account_id,
                                'type' => 'debit', // Debit returns money to the account
                                'operation_date' => $return_transaction->transaction_date,
                                'created_by' => Auth::user()->id,
                                'transaction_id' => $return_transaction->id,
                                'transaction_payment_id' => null,
                                'note' => 'Purchase Return Refund - Proportional to payment',
                                'business_id' => $business_id
                            ];
                            
                            AccountTransaction::createAccountTransaction($refund_data);
                        }
                    }
                }
            }


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
                'msg' => __('messages.something_went_wrong') . ' ' . $e->getMessage()
            ];
        }

        return redirect()->route('purchase-return.index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (!auth()->user()->can('purchase.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $business_id = auth()->user()->business_id ?? 1;

            $purchase_return = Transaction::where('id', $id)
                ->where('business_id', $business_id)
                ->where('type', 'purchase_return')
                ->with(['purchase_lines'])
                ->first();

            // Check for review date here...

            DB::beginTransaction();

            if (empty($purchase_return->return_parent_id)) {
                // Handling combined purchase return deletion (if applicable)
                $delete_purchase_lines = $purchase_return->purchase_lines;
                foreach ($delete_purchase_lines as $purchase_line) {
                     // Reverse the return (Increase Stock)
                     $this->productUtil->updateProductQuantity($purchase_return->location_id, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity_returned, 0, null, false);
                     
                     $store = Store::where('business_id', $business_id)->first();
                     $store_id = $store ? $store->id : 1;
                     $this->productUtil->updateProductQuantityStore($purchase_return->location_id, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity_returned, $store_id);
                }
                PurchaseLine::where('transaction_id', $purchase_return->id)->delete();
            } else {
                $parent_purchase = Transaction::where('id', $purchase_return->return_parent_id)
                    ->where('business_id', $business_id)
                    ->where('type', 'purchase')
                    ->with(['purchase_lines'])
                    ->first();

                $updated_purchase_lines = $parent_purchase->purchase_lines;
                foreach ($updated_purchase_lines as $purchase_line) {
                    // Reverse the return (Increase Stock) -> Passing new=0, old=returned triggers decrement in `decrease` logic? 
                    // Actually, if we use decreaseProductQuantity with new=0, old=returned: diff = -returned. Decrement(-returned) = Increment(returned).
                    // So stock increases back.
                    // Reverse the return (Increase Physical Stock -> Decrease Value)
                    $this->productUtil->updateProductQuantity(
                        $parent_purchase->location_id, 
                        $purchase_line->product_id, 
                        $purchase_line->variation_id, 
                        $purchase_line->quantity_returned, 
                        0,
                        null,
                        false
                    );
                    
                    $store_id = $parent_purchase->store_id ?? 1;
                    
                    $this->productUtil->updateProductQuantityStore(
                        $parent_purchase->location_id, 
                        $purchase_line->product_id, 
                        $purchase_line->variation_id, 
                        $purchase_line->quantity_returned, 
                        $store_id
                    );
    
                    $purchase_line->quantity_returned = 0;
                    $purchase_line->save();
                }

                // Revert parent status to received if it was fully returned
                if ($parent_purchase->status == 'ordered') {
                    $parent_purchase->status = 'received';
                    $parent_purchase->save();
                }
                
                // Update parent payment status
                $this->transactionUtil->updatePaymentStatus($parent_purchase->id, $parent_purchase->final_total);
            }

            //Delete Transaction
            $purchase_return->delete();

            //Delete account transactions - using instance deletion to trigger balance reversal
            AccountTransaction::where('transaction_id', $id)->get()->each->delete();
            ContactLedger::where('transaction_id', $id)->delete();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('Purchase return deleted successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong') . ' ' . $e->getMessage()
            ];
        }

        return $output;
    }
}

<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BusinessLocation;
use Modules\Contacts\Models\Contact;
use App\Models\TaxRate;
use App\Models\Category;
use App\Models\Brands;
use App\Models\Account;
use App\Models\AccountTransaction;
use Modules\Contacts\Models\ContactGroup;
use Modules\Contacts\Models\Transaction;
use Modules\Contacts\Models\TransactionPayment;
use App\Models\TransactionSellLine;
use App\Product;
use App\Variation;
use App\Store;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ContactUtil;
use Modules\Logistics\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\InvoiceScheme;
use App\SellingPriceGroup;
use App\TypesOfService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ContactLedger;
use App\Models\User;

class SalesController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $businessUtil;
    protected $moduleUtil;
    protected $contactUtil;
    protected $dummyPaymentLine;

    public function __construct(
        ProductUtil $productUtil,
        TransactionUtil $transactionUtil,
        BusinessUtil $businessUtil,
        ModuleUtil $moduleUtil,
        ContactUtil $contactUtil
    ) {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->contactUtil = $contactUtil;
        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
        $customers = Contact::customersDropdown($business_id, false);
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        return view('sales::index', compact('customers', 'business_locations'));
    }

    public function roundQuantity($quantity)
    {
        $quantity_precision = session('business.quantity_precision', 2);

        return round($quantity, $quantity_precision);
    }

    /**
     * Display list of shipments.
     *
     * @return \Illuminate\Http\Response
     */
    public function shipments()
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('sales::shipments')->with(compact('shipping_statuses'));
    }

    /**
     * Shows modal to edit shipping details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editShipping($id)
    {
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;

        $transaction = Transaction::where('business_id', $business_id)
            ->findOrFail($id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('sales::partials.edit_shipping')
            ->with(compact('transaction', 'shipping_statuses'));
    }

    /**
     * Update shipping details.
     *
     * @param  Request $request
     * @param  int $id
     * @return array
     */
    public function updateShipping(Request $request, $id)
    {
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'shipping_details', 'shipping_address',
                'shipping_status', 'delivered_to'
            ]);
            $business_id = $request->session()->get('user.business_id') ?? 1;

            $transaction_obj = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->first();
            
            $transaction_obj->update($input);

            if (!empty($input['shipping_status'])) {
                $shipment = Shipment::where('transaction_id', $id)->first();
                if (!$shipment) {
                    $business = \App\Business::find($business_id);
                    Shipment::create([
                        'business_id' => $business_id,
                        'transaction_id' => $id,
                        'shipment_no' => $transaction_obj->invoice_no,
                        'vendor' => $business->name,
                        'vendor_country' => 'Ethiopia',
                        'incoterms' => 'EXW',
                        'port_of_loading' => 'Local',
                        'port_of_discharge' => 'Local',
                        'transport_mode' => 'truck',
                        'status' => 'pending',
                        'expected_arrival' => Carbon::now()->addDays(2),
                        'user_id' => auth()->id(),
                        'shipping_details' => $transaction_obj->shipping_details,
                        'shipping_address' => $transaction_obj->shipping_address,
                        'shipping_status' => $transaction_obj->shipping_status,
                        'delivered_to' => $transaction_obj->delivered_to,
                        'shipping_charges' => $transaction_obj->shipping_charges,
                    ]);
                } else {
                    $shipment->update([
                        'shipping_details' => $transaction_obj->shipping_details,
                        'shipping_address' => $transaction_obj->shipping_address,
                        'shipping_status' => $transaction_obj->shipping_status,
                        'delivered_to' => $transaction_obj->delivered_to,
                    ]);
                }
            }

            $output = [
                'success' => 1,
                'msg' => __("Updated Success")
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    public function draftIndex()
    {
        return view('sales::draft_index');
    }

    public function quotationIndex()
    {
        return view('sales::quotation_index');
    }

    /**
     * DataTables list for sales.
     */
    public function list(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;

        $sales = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id');

        if ($request->has('is_quotation') && !is_null($request->get('is_quotation'))) {
            $sales->where('transactions.is_quotation', $request->get('is_quotation'));
        }

        if ($request->has('is_pos') && !is_null($request->get('is_pos'))) {
            $sales->where('transactions.is_pos', $request->get('is_pos'));
        }

        if ($request->has('status') && !empty($request->get('status'))) {
            $sales->where('transactions.status', $request->get('status'));
        }

        if ($request->has('only_shipments') && !empty($request->get('only_shipments'))) {
            $sales->whereNotNull('transactions.shipping_status');
        }

        if ($request->has('shipping_status') && !empty($request->get('shipping_status'))) {
            $sales->where('transactions.shipping_status', $request->get('shipping_status'));
        }

        $sales->select([
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'contacts.name as customer_name',
                'contacts.is_default as is_walk_in',
                'business_locations.name as location_name',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.status as status',
                'transactions.shipping_status',
                'transactions.shipping_details',
                'transactions.delivered_to',
                DB::raw('(SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments AS tp WHERE tp.transaction_id=transactions.id) as total_paid')
            ]);

        return datatables()->of($sales)
            ->addColumn('action', function ($row) use ($request) {
                $html = '<div class="dropdown dropdown-action">
                            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('sales.show', $row->id) . '">
                                    <i class="fa-solid fa-eye m-r-5"></i> ' . __('View') . '
                                </a>';
                
                if ($request->has('only_shipments')) {
                    $html .= '<a class="dropdown-item edit_shipping" href="javascript:void(0)" data-href="' . route('sales.edit_shipping', $row->id) . '">
                                <i class="fa-solid fa-truck m-r-5"></i> ' . __('Edit Shipping') . '
                            </a>';
                } else {
                    $html .= '<a class="dropdown-item" href="' . route('sales.edit', $row->id) . '">
                                <i class="fa-solid fa-pencil m-r-5"></i> ' . __('Edit') . '
                            </a>';
                }

                $html .= '<form action="' . route('sales.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'' . __('Are you sure?') . '\');" style="display:inline">
                                    ' . csrf_field() . '
                                    ' . method_field("DELETE") . '
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-trash m-r-5"></i> ' . __('Delete') . '</button>
                                </form>
                            </div>
                        </div>';
                return $html;
            })
            ->editColumn('customer_name', function ($row) {
                $name = $row->customer_name ?? '';
                if ($row->is_walk_in == 1) {
                    return $name . ' <span class="badge bg-secondary">Walk-in</span>';
                }
                return $name;
            })
            ->editColumn('transaction_date', function ($row) {
                return $row->transaction_date ? \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d H:i') : '';
            })
            ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
            ->editColumn('total_paid', '<span class="display_currency" data-currency_symbol="true">{{$total_paid}}</span>')
            ->addColumn('total_due', function ($row) {
                $due = $row->final_total - ($row->total_paid ?? 0);
                return '<span class="display_currency" data-currency_symbol="true">' . number_format($due, 2) . '</span>';
            })
            ->editColumn('payment_status', function ($row) {
                $status = $row->payment_status;
                $class = 'bg-info';
                if ($status == 'due') $class = 'bg-danger';
                if ($status == 'partial') $class = 'bg-warning';
                if ($status == 'paid') $class = 'bg-success';
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->editColumn('shipping_status', function ($row) {
                $status = $row->shipping_status;
                $class = 'bg-info';
                if ($status == 'delivered') $class = 'bg-success';
                if ($status == 'cancelled') $class = 'bg-danger';
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->editColumn('status', function ($row) {
                $status = $row->status;
                $class = 'bg-light text-dark';
                if ($status == 'final') $class = 'bg-success';
                if ($status == 'draft') $class = 'bg-secondary';
                if ($status == 'order') $class = 'bg-info';
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->rawColumns(['action', 'customer_name', 'final_total', 'total_paid', 'total_due', 'payment_status', 'shipping_status', 'status', 'transaction_date'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;

        $taxes = TaxRate::where('business_id', $business_id)->get();
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        $customer_groups = ContactGroup::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id, false);
        
        $first_location = BusinessLocation::where('business_id', $business_id)->first();
        $payment_types = $this->productUtil->payment_types($first_location, true, false, false, false, true, "is_sale_enabled");
        
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        $invoice_no = $this->businessUtil->getFormNumber('sell');

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_scheme_id = InvoiceScheme::getDefault($business_id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $types_of_service = TypesOfService::forDropdown($business_id);

        return view('sales::create', compact(
            'invoice_no',
            'taxes',
            'business_locations',
            'customer_groups',
            'customers',
            'payment_types',
            'accounts',
            'invoice_schemes',
            'default_invoice_scheme_id',
            'shipping_statuses',
            'price_groups',
            'types_of_service'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findOrFail($id);

        $taxes = TaxRate::where('business_id', $business_id)->get();
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        $customer_groups = ContactGroup::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id, false);
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        
        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_scheme_id = InvoiceScheme::getDefault($business_id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $types_of_service = TypesOfService::forDropdown($business_id);
        
        $first_location = BusinessLocation::find($transaction->location_id);
        $payment_types = $this->productUtil->payment_types($first_location, true, false, false, false, true, "is_sale_enabled");

        return view('sales::edit', compact(
            'transaction',
            'taxes',
            'business_locations',
            'customer_groups',
            'customers',
            'payment_types',
            'accounts',
            'invoice_schemes',
            'default_invoice_scheme_id',
            'shipping_statuses',
            'price_groups',
            'types_of_service'
        ));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->findOrFail($id);

            // Validation (simplified)
            $validator = Validator::make($request->all(), [
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'final_total' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            $input = $request->except(['_token', '_method']);
            
            // Generate update logic similar to store, reusing TransactionUtil
            // Note: Update logic requires deep diffing which Utils usually handle if passed correct IDs.
            // For now, this is a basic implementation structure.
            
             // Map 'sales' to 'products' for Utils
            $sales = $request->input('sales', []);
            $input['products'] = $sales; 
            
            // Recalculate invoice total
             $discount = [
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'discount_amount' => $input['discount_amount'] ?? 0
            ];
            $invoice_total = $this->productUtil->calculateInvoiceTotal($sales, $input['tax_rate_id'] ?? null, $discount);
            
            // Update Transaction
            $transaction->fill($input);
            $transaction->total_before_tax = $invoice_total['total_before_tax'];
            $transaction->tax_amount = $invoice_total['tax'];
            $transaction->save();

            // Update Sell Lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $sales, $transaction->location_id);
            
            // Update Payments?
            // Payment update logic is complex. Utils have `createOrUpdatePaymentLines`.
            
             // VAT
             $this->transactionUtil->calculateAndUpdateVAT($transaction);

            DB::commit();

            return redirect()->route('sales.index')->with(notify(__('Sale updated successfully')));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sale: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $user_id = $request->session()->get('user.id') ?? 1;

            // Check for reviewed dates
            $reviewed = $this->transactionUtil->get_review($request->transaction_date, $request->transaction_date, $business_id);
            if (!empty($reviewed)) {
                $output = [
                    'success' => 0,
                    'msg' => "You can't make a sale for an already reviewed date",
                ];
                return redirect()->route('sales.index')->with('status', $output);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'location_id' => 'required',
                'store_id' => 'required',
                'final_total' => 'required',
                'sales' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $input = $request->except('_token');

            // Check Credit Limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);
             if ($is_credit_limit_exeeded !== false) {
                 $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                  $output = [
                    'success' => 0,
                    'msg' => __('Customer credit limit exeeded', ['credit_limit' => $credit_limit_amount]),
                ];
                 return redirect()->back()->with('status', $output)->withInput();
             }

            $input['business_id'] = $business_id;
            $input['created_by'] = $user_id;
            $input['is_quotation'] = 0;
            $input['status'] = $input['status'] ?? 'final';
            $input['type'] = 'sell';
            $input['payment_status'] = 'due';
            
            // Set default values for fields expected by createSellTransaction
            $input['is_duplicate'] = $input['is_duplicate'] ?? 0;
            $input['customer_group_id'] = $input['customer_group_id'] ?? null;
            $input['commission_agent'] = $input['commission_agent'] ?? null;
            $input['is_direct_sale'] = $input['is_direct_sale'] ?? 0;
            $input['is_customer_order'] = $input['is_customer_order'] ?? 0;
            
            if ($input['status'] == 'order') {
                $input['is_customer_order'] = 1;
            }
            
            
            // Format date
            if (!empty($input['transaction_date'])) {
                 if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['transaction_date'])) {
                    $input['transaction_date'] = $input['transaction_date'] . ' ' . date('H:i:s');
                 }
            } else {
                $input['transaction_date'] = Carbon::now();
            }
            
            // Generate invoice number if empty
            if(empty($input['invoice_no'])){
                 $input['invoice_no'] = $this->businessUtil->getFormNumber('sell');
            }

            // Map 'sales' to 'products' for Utils
            $sales = $request->input('sales', []);
            $input['products'] = $sales; 

            $discount = [
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'discount_amount' => $input['discount_amount'] ?? 0
            ];
            
            $invoice_total = $this->productUtil->calculateInvoiceTotal($sales, $input['tax_rate_id'] ?? null, $discount);
            
            // Determine credit sale
            $is_credit_sale = 0;
            $payments = $request->input('payment', []);
            if (!empty($payments)) {
                if (isset($payments[0]['method']) && $payments[0]['method'] == 'credit_sale') {
                    $is_credit_sale = 1;
                }
            }
            $input['is_credit_sale'] = $is_credit_sale;

            // Create Transaction
            $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

            // Create Sell Lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $sales, $transaction->location_id);

            // Create Payment Lines
            if(!empty($payments) && !$is_credit_sale) {
                 $this->transactionUtil->createOrUpdatePaymentLines($transaction, $payments, $business_id, $user_id, true, $transaction->status);
            }

            // Accounting for Credit Sale
            if ($is_credit_sale) {
                  $account = Account::where('business_id', $business_id)->where('name', 'Accounts Receivable')->first();
                  $acc_id = $account ? $account->id : null;
                  
                  if($acc_id) {
                       $account_transaction_data = [
                            'amount' => $transaction->final_total,
                            'account_id' => $acc_id,
                            'type' => 'debit',
                            'sub_type' => '',
                            'operation_date' => $transaction->transaction_date,
                            'created_by' => $transaction->created_by,
                            'transaction_id' => $transaction->id,
                            'transaction_payment_id' => null,
                        ];
                        // Create AR transaction
                        $this->transactionUtil->createAccountTransaction($transaction, 'debit', $acc_id);
                        
                        // Manage Stock Account
                        $this->transactionUtil->manageStockAccount($transaction, $account_transaction_data, 'credit', $transaction->final_total);
                        $this->transactionUtil->createCostofGoodsSoldTransaction($transaction, null, 'debit');
                        $this->transactionUtil->createSaleIncomeTransaction($transaction, null, 'credit');
                  }
                  
                   $this->createContactLedger($transaction, 'debit');
            }

            // Update Stock
            foreach ($sales as $product) {
                 if (!empty($product['variation_id']) && !empty($product['enable_stock'])) {
                    $decrease_qty = $this->productUtil->num_uf($product['quantity']);
                     if (!empty($product['base_unit_multiplier'])) {
                        $decrease_qty *= $product['base_unit_multiplier'];
                    }

                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $transaction->location_id,
                        $decrease_qty,
                        0,
                        'decrease',
                        $transaction->store_id
                    );

                    $this->productUtil->decreaseProductQuantityStore(
                        $product['product_id'],
                        $product['variation_id'],
                        $transaction->location_id,
                        $decrease_qty,
                        $transaction->store_id,
                        "decrease",
                        0
                    );
                }
            }

            // Map Purchase Sell (FIFO)
             $business_details = $this->businessUtil->getDetails($business_id);
             $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
             $business_data = [
                 'id' => $business_id,
                 'accounting_method' => $request->session()->get('business.accounting_method'),
                 'location_id' => $input['location_id'],
                 'pos_settings' => $pos_settings
             ];
             $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            
             // VAT
             $this->transactionUtil->calculateAndUpdateVAT($transaction);
             
             // Update Payment Status
             $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            DB::commit();

            $notification = notify(__('Sale has been created successfully'));
            return redirect()->route('sales.index')->with($notification);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storing sale: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            return redirect()->back()
                ->with('error', __('messages.something_went_wrong') . ': ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get payment accounts based on payment method
     */
    public function getPaymentAccounts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
        $payment_method = $request->payment_method;
        
        $query = Account::where('accounts.business_id', $business_id);
        
        // Filter accounts based on payment method
        if ($payment_method === 'bank_transfer' || $payment_method === 'card') {
            // Bank accounts for bank transfers and card payments
            $query->leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
                  ->where('account_groups.name', 'Bank Account');
        } elseif ($payment_method === 'cheque') {
            // CPC accounts for cheque payments
            $query->leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
                  ->where('account_groups.name', 'CPC');
        }
        // For cash and other methods, return all accounts or specific cash accounts if needed
        
        $accounts = $query->pluck('accounts.name', 'accounts.id');
        
        return response()->json($accounts);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
        
        $sale = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with([
                'contact', 
                'sell_lines', 
                'sell_lines.product', 
                'sell_lines.product.unit', 
                'sell_lines.variations', 
                'payment_lines', 
                'location'
            ])
            ->firstOrFail();

        foreach ($sale->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sale->sell_lines[$key] = $formated_sell_line;
            }
        }

        $payment_types = $this->productUtil->payment_types($sale->location, true, false, false, false, true, "is_sale_enabled");

        $order_taxes = [];
        if (!empty($sale->tax)) {
            if ($sale->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sale->tax, $sale->tax_amount));
            } else {
                $order_taxes[$sale->tax->name] = $sale->tax_amount;
            }
        }

        return view('sales::show', compact('sale', 'payment_types', 'order_taxes'));
    }



    public function getProducts(Request $request)
    {
        if ($request->ajax()) {
            $term = $request->term ?? '';
            $location_id = $request->location_id;
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $store_id = $request->store_id;

            $products = $this->productUtil->filterProductPos(
                $business_id, 
                $term, 
                $location_id, 
                false, 
                null, 
                [], 
                ['name', 'sku', 'sub_sku'], 
                false,
                'like',
                $store_id,
                null,
                null
            );

            $results = [];
            foreach ($products as $product) {
                $text = $product->name;
                if ($product->type == 'variable' && $product->variation != 'DUMMY') {
                    $text .= ' (' . $product->variation . ')';
                }
                
                $results[] = [
                    'product_id' => $product->product_id,
                    'variation_id' => $product->variation_id,
                    'text' => $text . ' (' . $product->sub_sku . ')',
                    'sub_sku' => $product->sub_sku,
                    'image' => $product->image_url,
                    'selling_price' => $product->selling_price,
                    'current_stock' => $product->current_stock,
                    'unit' => $product->unit,
                ];
            }

            return response()->json($results);
        }
    }

    /**
     * Get sell entry row for AJAX.
     */
    public function getSellEntryRow(Request $request)
    {
        $product_id = $request->input('product_id');
        $variation_id = $request->input('variation_id');
        $row_count = $request->input('row_count');
        $location_id = $request->input('location_id');
        $store_id = $request->input('store_id');
        $business_id = auth()->user()->business_id ?? 1;

        $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, true, $store_id);
        $variation = Variation::with(['product_variation'])->find($variation_id);
        $current_stock = $product->current_stock ?? 0;

        // Check if product is out of stock
        if ($current_stock <= 0) {
            return response()->json([
                'success' => false,
                'msg' => __('Out of Stock')
            ]);
        }

        $taxes = TaxRate::where('business_id', $business_id)->get();
        
        // Get sub units for unit selection
        $sub_units = [];
        if ($product && !empty($product->unit_id)) {
            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product_id);
        }

        return view('sales::partials.sell_entry_row', compact('product', 'variation', 'row_count', 'taxes', 'current_stock', 'sub_units'));
    }

    /**
     * Get payment row for AJAX.
     */
    public function getPaymentRow(Request $request)
    {
        $row_index = $request->input('row_index');
        $business_id = $request->session()->get('user.business_id');
        
        $first_location = BusinessLocation::where('business_id', $business_id)->first();
        $payment_types = $this->productUtil->payment_types($first_location, true, false, false, false, true, "is_sale_enabled");
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        return view('sales::partials.payment_row', compact('row_index', 'payment_types', 'accounts'));
    }
    public function createContactLedger($transaction, $type)
    {
        $account_transaction_data = [
            'contact_id'             => !empty($transaction) ? $transaction->contact_id : null,
            'amount'                 => $transaction->final_total,
            'type'                   => $type,
            'operation_date'         => $transaction->transaction_date,
            'created_by'             => $transaction->created_by,
            'transaction_id'         => $transaction->id,
            'transaction_payment_id' => null,
        ];
        ContactLedger::createContactLedger($account_transaction_data);
    }

    /**
     * Display a listing of over limit sales.
     *
     * @return \Illuminate\Http\Response
     */
    public function overLimitSales()
    {
        $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;

        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, false, false, false, true, "is_sale_enabled");
            
            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')
                ->leftJoin('users as u', 'transactions.requested_by', '=', 'u.id')
                ->leftJoin('users as ss', 'transactions.approved_user', '=', 'ss.id')
                ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.is_over_limit_credit_sale', 1)
                ->whereIn('transactions.status', ['final', 'order'])
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'contacts.mobile',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.over_limit_amount',
                    'transactions.customer_limit',
                    DB::raw("CONCAT(COALESCE(ss.firstname, ''),' ',COALESCE(ss.lastname, '')) as approved_by"),
                    DB::raw("CONCAT(COALESCE(u.firstname, ''),' ',COALESCE(u.lastname, '')) as requested_by"),
                    DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id=transactions.id) as total_paid'),
                    'bl.name as business_location',
                    DB::raw('COUNT(SR.id) as return_exists'),
                    DB::raw('COUNT( DISTINCT tsl.id) as total_items')
                )->with('payment_lines');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!empty(request()->input('payment_status'))) {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('approved_user'))) {
                $sells->where('transactions.approved_user', request()->input('approved_user'));
            }

            if (!empty(request()->input('invoice_no'))) {
                $sells->where('transactions.invoice_no', request()->input('invoice_no'));
            }

            $sells->groupBy('transactions.id');

            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        $html .= '<li><a href="#" data-href="' . route('sales.show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-external-link" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    function ($row) {
                        return '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="' . $row->final_total . '">' . number_format($row->final_total, 2) . '</span>';
                    }
                )
                ->editColumn(
                    'total_paid',
                    function ($row) {
                        return '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="' . $row->total_paid . '">' . number_format($row->total_paid, 2) . '</span>';
                    }
                )
                ->editColumn('transaction_date', function ($row) {
                    return \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d H:i:s');
                })
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sales::partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1 && $methods[0] != null) {
                        $payment_method = ucfirst(str_replace('_', ' ', $methods[0]));
                    } elseif ($count > 1) {
                        $payment_method = __('Checkout multi pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '">' . $payment_method . '</span>' : '';
                    
                    return $html;
                })
                ->editColumn('over_limit_amount', function ($row) {
                    return number_format($row->over_limit_amount, 2);
                })
                ->editColumn('customer_limit', function ($row) {
                    return number_format($row->customer_limit, 2);
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        return route('sales.show', [$row->id]);
                    }
                ]);

            $rawColumns = ['final_total', 'action', 'total_paid', 'payment_status', 'payment_methods'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        
        $invoice_nos = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('is_over_limit_credit_sale', '1')
            ->groupBy('invoice_no')
            ->pluck('invoice_no', 'invoice_no');
            
        $approved_users = Transaction::leftjoin('users', 'transactions.approved_user', 'users.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.approved_user', '!=', null)
            ->distinct('approved_user')
            ->pluck('users.username', 'users.id');
            
        return view('sales::over_limit_sales')
            ->with(compact('approved_users', 'business_locations', 'customers', 'invoice_nos'));
    }

    /**
     * Display a listing of the recurring invoices.
     *
     * @return \Illuminate\Http\Response
     */
    public function listSubscriptions()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_recurring', 1)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.subscription_no',
                    'bl.name as business_location',
                    'transactions.recur_parent_id',
                    'transactions.recur_stopped_on',
                    'transactions.is_recurring',
                    'transactions.recur_interval',
                    'transactions.recur_interval_type',
                    'transactions.recur_repetitions'
                )->with(['subscription_invoices']);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            return Datatables::of($sells)
                ->addColumn('action', function ($row) {
                    $html = '';
                    if ($row->is_recurring == 1 && auth()->user()->can("sell.update")) {
                        $link_text = !empty($row->recur_stopped_on) ? __('Start Subscription') : __('Stop Subscription');
                        $link_class = !empty($row->recur_stopped_on) ? 'btn-success' : 'btn-danger';
                        
                        $html .= '<a href="' . route('sales.subscriptions.toggle', [$row->id]) . '" class="toggle_recurring_invoice btn btn-xs ' . $link_class . '"><i class="fa fa-power-off"></i> ' . $link_text . '</a>';
                        
                        if ($row->is_direct_sale == 0) {
                            $html .= ' <a target="_blank" class="btn btn-xs btn-primary" href="' . route('sales.pos.edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("Edit") . '</a>';
                        } else {
                            $html .= ' <a target="_blank" class="btn btn-xs btn-primary" href="' . route('sales.edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("Edit") . '</a>';
                        }
                    }
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('transaction_date', function ($row) {
                    return $this->transactionUtil->format_date($row->transaction_date);
                })
                ->editColumn('recur_interval', function ($row) {
                    $type = $row->recur_interval == 1 ? \Illuminate\Support\Str::singular(__($row->recur_interval_type)) : __($row->recur_interval_type);
                    return $row->recur_interval . ' ' . $type;
                })
                ->editColumn('recur_repetitions', function ($row) {
                    return !empty($row->recur_repetitions) ? $row->recur_repetitions : __('Infinite');
                })
                ->addColumn('subscription_invoices', function ($row) {
                    $invoices = [];
                    if (!empty($row->subscription_invoices)) {
                        $invoices = $row->subscription_invoices->pluck('invoice_no')->toArray();
                    }
                    $html = '';
                    if (!empty($invoices)) {
                        $imploded_invoices = '<span class="label bg-info">' . implode('</span>, <span class="label bg-info">', $invoices) . '</span>';
                        $html .= '<small>' . $imploded_invoices . '</small>';
                    }
                    return !empty($html) ? $html : '-';
                })
                ->addColumn('last_generated', function ($row) {
                    $last_generated_date = !empty($row->subscription_invoices) ? $row->subscription_invoices->max('created_at') : null;
                    return !empty($last_generated_date) ? \Carbon\Carbon::parse($last_generated_date)->diffForHumans() : '-';
                })
                ->addColumn('upcoming_invoice', function ($row) {
                    if (empty($row->recur_stopped_on)) {
                        $last_generated = !empty($row->subscription_invoices) ? Carbon::parse($row->subscription_invoices->max('transaction_date')) : Carbon::parse($row->transaction_date);
                        $upcoming_invoice = \Carbon\Carbon::now();
                        if ($row->recur_interval_type == 'days') {
                            $upcoming_invoice = $last_generated->addDays($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'months') {
                            $upcoming_invoice = $last_generated->addMonths($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'years') {
                            $upcoming_invoice = $last_generated->addYears($row->recur_interval);
                        }
                        return $this->transactionUtil->format_date($upcoming_invoice);
                    }
                    return '-';
                })
                ->rawColumns(['action', 'subscription_invoices'])
                ->make(true);
        }

        return view('sales::subscriptions');
    }

    /**
     * Toggle recurring invoice status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleRecurringInvoices($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id') ?? (auth()->user()->business_id ?? 1);;
            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->where('is_recurring', 1)
                ->findOrFail($id);

            if (empty($transaction->recur_stopped_on)) {
                $transaction->recur_stopped_on = Carbon::now();
            } else {
                $transaction->recur_stopped_on = null;
            }
            $transaction->save();
            
            $output = [
                'success' => 1,
                'msg'     => __('Updated success'),
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg'     => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}

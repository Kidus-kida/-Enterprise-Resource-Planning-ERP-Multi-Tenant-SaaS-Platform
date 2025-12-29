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
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\TransactionSellLine;
use App\Product;
use App\Variation;
use App\Store;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ContactUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\InvoiceScheme;
use App\SellingPriceGroup;
use App\TypesOfService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ContactLedger;

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
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $customers = Contact::customersDropdown($business_id, false);
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        return view('sales::index', compact('customers', 'business_locations'));
    }

    /**
     * DataTables list for sales.
     */
    public function list(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $sales = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id')
            ->select([
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'contacts.name as customer_name',
                'business_locations.name as location_name',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.status as status',
                DB::raw('(SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments AS tp WHERE tp.transaction_id=transactions.id) as total_paid')
            ]);

        return datatables()->of($sales)
            ->addColumn('action', function ($row) {
                $html = '<div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Action
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="' . route('sales.show', [$row->id]) . '"><i class="fas fa-eye"></i> View</a></li>
                                <li><a class="dropdown-item" href="' . route('sales.edit', [$row->id]) . '"><i class="fas fa-edit"></i> Edit</a></li>
                                <li><a class="dropdown-item delete-sale" href="' . route('sales.destroy', [$row->id]) . '"><i class="fas fa-trash"></i> Delete</a></li>
                            </ul>
                        </div>';
                return $html;
            })
            ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
            ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
            ->editColumn('total_paid', '<span class="display_currency" data-currency_symbol="true">{{$total_paid}}</span>')
            ->addColumn('total_due', function ($row) {
                $due = $row->final_total - ($row->total_paid ?? 0);
                return '<span class="display_currency" data-currency_symbol="true">' . $due . '</span>';
            })
            ->editColumn('payment_status', function ($row) {
                $status = $row->payment_status;
                $class = 'bg-info';
                if ($status == 'due') $class = 'bg-danger';
                if ($status == 'partial') $class = 'bg-warning';
                if ($status == 'paid') $class = 'bg-success';
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->rawColumns(['action', 'final_total', 'total_paid', 'total_due', 'payment_status'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

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
        $business_id = request()->session()->get('user.business_id');
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
                'discount_type' => $input['discount_type'],
                'discount_amount' => $input['discount_amount']
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
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount]),
                ];
                 return redirect()->back()->with('status', $output)->withInput();
             }

            $input['business_id'] = $business_id;
            $input['created_by'] = $user_id;
            $input['is_quotation'] = 0;
            $input['status'] = $input['status'] ?? 'final';
            $input['type'] = 'sell';
            $input['payment_status'] = 'due';
            
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
                'discount_type' => $input['discount_type'],
                'discount_amount' => $input['discount_amount']
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
     * Show the specified resource.
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        
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
        $business_id = $request->session()->get('user.business_id');

        $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, true, $store_id);
        $variation = Variation::with(['product_variation'])->find($variation_id);
        $current_stock = $product->current_stock ?? 0;
        $taxes = TaxRate::where('business_id', $business_id)->get();

        return view('sales::partials.sell_entry_row', compact('product', 'variation', 'row_count', 'taxes', 'current_stock'));
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
}

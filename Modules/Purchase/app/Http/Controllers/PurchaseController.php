<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use Modules\Contacts\Models\SupplierProductMapping;
use Modules\Contacts\Models\ContactGroup;
use Modules\Accounting\Models\Account;
use App\Models\BusinessLocation;
use App\Models\TaxRate;
use App\Models\Product;
use Modules\Contacts\Models\Contact;
use App\Store;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Contacts\Models\Transaction;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Logistics\Models\Shipment;
use Carbon\Carbon;
use Modules\Contacts\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Category;
use App\Brands;
use App\Models\Variation;
use App\Models\Unit;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    public function __construct(ProductUtil $productUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'cheque_date' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', 'account_id' => ''
        ];
    }

    public function getChequeList(Request $request)
    {
        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');
            $start_date = !empty($request->start_date) ? Carbon::parse($request->start_date)->format('Y-m-d') : date('Y-m-d');
            $end_date = !empty($request->end_date) ? Carbon::parse($request->end_date)->format('Y-m-d') : date('Y-m-d');
            
            $cheque_account = Account::getAccountByAccountName('Cheques in Hand');
            
            // If Cheques in Hand account doesn't exist, we can't find cheques
            if (!$cheque_account) {
                 return view('purchase::partials.cheque_list', ['cheque_lists' => []]);
            }

            $query = AccountTransaction::leftjoin('transaction_payments', 'account_transactions.transaction_payment_id', 'transaction_payments.id')
                ->leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
                ->leftjoin('transactions', 'transaction_payments.transaction_id', 'transactions.id')
                ->leftjoin('contacts', 'transaction_payments.payment_for', 'contacts.id')
                ->where('account_transactions.account_id', $cheque_account->id)
                ->where('transaction_payments.method', 'cheque')
                ->where('account_transactions.type', 'debit')
                ->where('transaction_payments.is_deposited', 0)
                ->whereNull('account_transactions.deleted_at')
                ->whereNull('transaction_payments.deleted_at');
                
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereDate('transaction_payments.cheque_date', '>=', $start_date);
                $query->whereDate('transaction_payments.cheque_date', '<=', $end_date);
            }
            
            $cheque_lists = $query->select(
                'contacts.name as customer_name',
                'transaction_payments.cheque_number',
                'transaction_payments.cheque_date',
                'transaction_payments.bank_name',
                'account_transactions.amount',
                'account_transactions.id',
                'transactions.id as t_id'
            )->get();

            return view('purchase::partials.cheque_list', compact('cheque_lists'));
        }
    }

    /**
     * Display a listing of the resource for DataTables.
     */
    public function list()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;


        $purchases = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase');

        if (request()->has('only_shipments')) {
            $purchases->whereNotNull('transactions.shipping_status');
        }

        if (request()->has('shipping_status')) {
            $purchases->where('transactions.shipping_status', request()->get('shipping_status'));
        }

        $purchases->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'contacts.name as supplier_name',
                'transactions.status',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.shipping_status',
                'transactions.shipping_details',
                'transactions.delivered_to',
                DB::raw('COALESCE((SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.deleted_at IS NULL), 0) as amount_paid')
            );
            
        return DataTables::of($purchases)
            ->addColumn('action', function ($row) {
                $html = '<div class="dropdown dropdown-action">
                            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('purchase.show', $row->id) . '">
                                    <i class="fa-solid fa-eye m-r-5"></i> ' . __('View') . '
                                </a>';

                if (request()->has('only_shipments')) {
                    $html .= '<a class="dropdown-item edit_shipping" href="javascript:void(0)" data-href="' . route('purchase.edit_shipping', $row->id) . '">
                                <i class="fa-solid fa-truck m-r-5"></i> ' . __('lang_v1.edit_shipping') . '
                            </a>';
                } else {
                    $html .= '<a class="dropdown-item" href="' . route('purchase.edit', $row->id) . '">
                                <i class="fa-solid fa-pencil m-r-5"></i> ' . __('Edit') . '
                            </a>
                            <a class="dropdown-item" href="' . route('purchase-return.add', $row->id) . '">
                                <i class="fa fa-undo m-r-5"></i> ' . __('Purchase Return') . '
                            </a>';
                }

                $html .= '<form action="' . route('purchase.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'' . __('Are you sure?') . '\');" style="display:inline">
                                    ' . csrf_field() . '
                                    ' . method_field("DELETE") . '
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-trash m-r-5"></i> ' . __('Delete') . '</button>
                                </form>
                            </div>
                        </div>';
                return $html;
            })
            ->editColumn('transaction_date', function ($row) {
                return \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d');
            })
            ->editColumn('status', function ($row) {
                    return '<span class="badge" style="color: black">' . ucfirst($row->status) . '</span>';
            })
                ->editColumn('payment_status', function ($row) {
                    return '<span class="badge" style="color: black">' . ucfirst($row->payment_status) . '</span>';
            })
            ->editColumn('final_total', function ($row) {
                return number_format((float) $row->final_total, 2);
            })
            ->addColumn('due', function ($row) {
                $paid = (float) $row->amount_paid;
                $total = (float) $row->final_total;
                return number_format($total - $paid, 2);
            })
            ->editColumn('shipping_status', function ($row) {
                $status = $row->shipping_status;
                $class = 'bg-info';
                if ($status == 'delivered') $class = 'bg-success';
                if ($status == 'cancelled') $class = 'bg-danger';
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->rawColumns(['action', 'status', 'payment_status', 'shipping_status'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $purchases = [];
        if (request('view') == 'grid') {
            $purchases = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with(['contact'])
                ->select(
                    'transactions.*',
                    DB::raw('COALESCE((SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.deleted_at IS NULL), 0) as amount_paid')
                )
                ->latest()
                ->get();
        }
        
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase::index', compact('purchases', 'business_locations', 'suppliers', 'orderStatuses'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $taxes = TaxRate::where('business_id', $business_id)->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        
        $customer_groups = ContactGroup::forDropdown($business_id);

        $first_location = BusinessLocation::where('business_id', $business_id)->first();
        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types($first_location, true, true, false, false, true, "is_purchase_enabled");

        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $bank_group_accounts = Account::leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
            ->where('accounts.business_id', $business_id)
            ->where('account_groups.name', 'Bank Account')
            ->pluck('accounts.name', 'accounts.id');
            
        $cpc_accounts = Account::leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
            ->where('accounts.business_id', $business_id)
            ->where('account_groups.name', 'CPC')
            ->pluck('accounts.name', 'accounts.id');

        $purchase_no = $this->businessUtil->getFormNumber('purchase');
        
        $suppliers = Contact::suppliersDropdown($business_id, false);

        return view('purchase::create', compact(
            'purchase_no',
            'taxes',
            'orderStatuses',
            'business_locations',
            'currency_details',
            'default_purchase_status',
            'customer_groups',
            'types',
            'payment_types',
            'accounts',
            'bank_group_accounts',
            'cpc_accounts',
            'suppliers',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $business_id = request()->session()->get('user.business_id') ?? 1;
            $user_id = request()->session()->get('user.id') ?? 1;

            // Check for reviewed dates
            $reviewed = $this->transactionUtil->get_review($request->transaction_date, $request->transaction_date, $business_id);
            
            if (!empty($reviewed)) {
                $output = [
                    'success' => 0,
                    'msg' => "You can't make a purchase for an already reviewed date",
                ];
                return redirect('purchases')->with('status', $output);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'store_id' => 'required',
                'ref_no' => [
                    'required',
                    Rule::unique('transactions', 'ref_no')
                        ->where(fn ($q) => $q->where('contact_id', $request->contact_id)),
                ],
                'document' => 'nullable|file|max:' . (config('constants.document_size_limit', 5000000) / 1000),
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            DB::beginTransaction();

            $transaction_data = $request->only([
                'ref_no', 'status', 'contact_id', 'transaction_date', 'total_before_tax',
                'location_id', 'discount_amount', 'discount_type', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges',
                'final_total', 'additional_notes', 'pay_term_number', 'pay_term_type',
                'invoice_no', 'invoice_date', 'is_vat', 'exchange_rate'
            ]);

            $exchange_rate = $transaction_data['exchange_rate'] ?? 1;

            // Unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if (!empty($transaction_data['discount_type']) && $transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif (!empty($transaction_data['discount_type']) && $transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'] ?? 0, $currency_details) * $exchange_rate;
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'] ?? 0, $currency_details) * $exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['store_id'] = $request->input('store_id');
            
            // Check if transaction_date is in Y-m-d format (from HTML5 date input)
            if (!empty($transaction_data['transaction_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $transaction_data['transaction_date'])) {
                $transaction_data['transaction_date'] = $transaction_data['transaction_date'] . ' ' . date('H:i:s');
            } else {
                $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            }
            
            $transaction_data['exchange_rate'] = $exchange_rate;

            // Upload document
            $transaction_data['document'] = $this->productUtil->uploadFile($request, 'document', 'documents');

            // Update reference count and generate reference number
            if (empty($transaction_data['ref_no'])) {
                $ref_count = $this->productUtil->setAndGetReferenceCount('purchase', $business_id);
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber('purchase', $ref_count, $business_id);
            }

            $taxes = TaxRate::where('business_id', $business_id)->first();
            $tax_id = !empty($taxes) ? $taxes->id : 1;
            $transaction_data['tax_id'] = $tax_id;

            $transaction = Transaction::create($transaction_data);

            if (!empty($transaction_data['shipping_status'])) {
                $contact = Contact::find($transaction->contact_id);
                Shipment::create([
                    'business_id' => $business_id,
                    'transaction_id' => $transaction->id,
                    'shipment_no' => $transaction->ref_no,
                    'vendor' => !empty($contact) ? $contact->name : 'Supplier',
                    'vendor_country' => !empty($contact) ? ($contact->country ?? 'Unknown') : 'Unknown',
                    'incoterms' => 'CIF',
                    'port_of_loading' => 'Foreign',
                    'port_of_discharge' => 'Local',
                    'transport_mode' => 'sea',
                    'status' => 'pending',
                    'expected_arrival' => Carbon::parse($transaction->transaction_date)->addDays(30),
                    'user_id' => $user_id,
                    'shipping_details' => $transaction->shipping_details,
                    'shipping_address' => $transaction->shipping_address,
                    'shipping_status' => $transaction->shipping_status,
                    'delivered_to' => $transaction->delivered_to,
                    'shipping_charges' => $transaction->shipping_charges,
                ]);
            }

            // Create purchase lines
            $purchases = $request->input('purchases', []);
            $store_id = $request->input('store_id');
            if (!empty($purchases)) {
                $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing, $store_id);
            }

            // Process selected cheques (Endorsing cheques)
            if ($request->has('select_cheques')) {
                foreach ($request->select_cheques as $select_cheque) {
                    if (!empty($select_cheque)) {
                        $account_transaction = AccountTransaction::find($select_cheque);
                        
                        if ($account_transaction) {
                            $transaction_payment = TransactionPayment::find($account_transaction->transaction_payment_id);
                            
                            if (!empty($transaction_payment)) {
                                $amount = $this->productUtil->num_uf($account_transaction->amount, $currency_details);
                                
                                if ($amount > 0) {
                                    // Create credit transaction for the cheque account
                                    $credit_data = [
                                        'amount' => $amount,
                                        'account_id' => $account_transaction->account_id,
                                        'transaction_id' => $transaction->id,
                                        'type' => 'credit',
                                        'sub_type' => null,
                                        'operation_date' => $transaction_data['transaction_date'],
                                        'created_by' => $user_id,
                                        'transaction_payment_id' => $transaction_payment->id,
                                        'note' => null,
                                        'attachment' => null
                                    ];
                                    
                                    AccountTransaction::create($credit_data);
                                    
                                    // Mark cheque as deposited/used
                                    $transaction_payment->is_deposited = 1;
                                    $transaction_payment->save();
                                }
                            }
                        }
                    }
                }
            }

            // Add Payments
            $payments = $request->input('payment', []);
            if (!empty($payments)) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $payments, $business_id, $user_id, true, $transaction->status);
            }

            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            // Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            // Calculate and update VAT
            $this->transactionUtil->calculateAndUpdateVAT($transaction);

            DB::commit();

            $notification = notify(__('Purchase has been created successfully'));
            return redirect()->route('purchase.index')->with($notification);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storing purchase: ' . $e->getMessage() . ' Line: ' . $e->getLine());
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
        $transaction = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['contact', 'purchase_lines', 'payments']) 
            ->firstOrFail();

        return view('purchase::show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $transaction = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->firstOrFail();

        return view('purchase::edit', compact('transaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Get purchase entry row HTML via AJAX
     */
    public function getPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');
            
            $business_id = request()->session()->get('user.business_id') ?? 1;
            
            $product = Product::leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                                ->where('products.id',$product_id)
                                ->select('c1.name as category_name')
                                ->first();
                                
            if(!empty($product) && $product->category_name == "Fuel"){
                 $fuel_tanks = FuelTank::where('product_id', $product_id)->where('location_id', $location_id)->get();
                 $current_stock = 0;
                 foreach($fuel_tanks as $tank){
                     $current_stock +=  $this->transactionUtil->getTankBalanceById($tank->id);
                 }
            }else{
                $current_stock = DB::table('variation_location_details')->where('variation_id', $variation_id)->select('qty_available')->first();
                $current_stock = !empty($current_stock) ? $current_stock->qty_available : 0;
            }

            
            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit'])
                    ->first();
                $fuel_category_id = Category::where('business_id', $business_id)->where('name', 'Fuel')->first();
                $is_fuel_category = 0;
                if (!empty($fuel_category_id)) {
                    if ($product->category->id == $fuel_category_id->id) {
                        $is_fuel_category = 1;
                    }
                }
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with(['product_variation']);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $variations =  $query->get();

                $taxes = TaxRate::where('business_id', $business_id)
                    ->get();
                $temp_qty = null;
                $purchase_pos = (bool)$request->purchase_pos ? 1 : 0;
                $enable_petro_module =  $this->moduleUtil->hasThePermissionInSubscription($business_id, 'enable_petro_module');
                //If brands, category are enabled then send else false.
                $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id, $enable_petro_module) : false;
                $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::where('business_id', $business_id)
                    ->pluck('name', 'id')
                    ->prepend(__('All Brands'), 'all') : false;

    
                $active = $this->moduleUtil->hasThePermissionInSubscription($business_id, 'price_changes_module');
                $purchase_zero = auth()->user()->can('purchase_zero');
                
                $enable_lot_number = request()->session()->get('business.enable_lot_number');
                $enable_product_expiry = request()->session()->get('business.enable_product_expiry');

                return view('purchase::partials.purchase_entry_row')
                    ->with(compact(
                        'active',
                        'categories',
                        'brands',
                        'purchase_pos',
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'current_stock',
                        'temp_qty',
                        'is_fuel_category',
                        'purchase_zero',
                        'enable_lot_number',
                        'enable_product_expiry'
                    ));
            }
        }
    }
    /**
     * Get products list for autocomplete
     */
    public function getProducts(Request $request)
    {
        $term = $request->term ?? '';
        $location_id = $request->location_id;
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $products = Product::leftJoin('variations', 'products.id', '=', 'variations.product_id')
            ->where('products.business_id', $business_id)
            ->where('products.type', '!=', 'modifier')
            ->where(function ($query) use ($term) {
                $query->where('products.name', 'like', '%' . $term . '%')
                    ->orWhere('products.sku', 'like', '%' . $term . '%')
                    ->orWhere('variations.sub_sku', 'like', '%' . $term . '%');
            })
            ->select(
                'products.id as product_id',
                'products.name',
                'products.sku',
                'variations.id as variation_id',
                'variations.name as variation_name',
                'variations.sub_sku'
            )
            ->limit(20)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $text = $product->name;
            if ($product->variation_name) {
                $text .= ' - ' . $product->variation_name;
            }
            $text .= ' (' . ($product->sub_sku ?: $product->sku) . ')';

            $results[] = [
                'id' => $product->product_id . '_' . $product->variation_id,
                'text' => $text,
                'product_id' => $product->product_id,
                'variation_id' => $product->variation_id,
            ];
        }

        return response()->json($results);
    }

    /**
     * Get payment row HTML via AJAX
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $row_index = $request->row_index ?? 0;
        $location_id = $request->location_id;
        
        // Get payment types
        $payment_types = $this->productUtil->payment_types($location_id, true, true, false, false, true);
        
        // Get accounts for payment
        $accounts = Account::where('business_id', $business_id)->pluck('name', 'id');
        
        return view('purchase::partials.payment_row', compact(
            'row_index',
            'payment_types',
            'accounts'
        ));
    }

    /**
     * Get payment accounts based on payment method
     */
    public function getPaymentAccounts(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $payment_method = $request->payment_method;
        
        $query = Account::where('business_id', $business_id);
        
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
        // For cash and other methods, return all accounts
        
        $accounts = $query->pluck('accounts.name', 'accounts.id');
        
        return response()->json($accounts);
    }

    /**
     * Get suppliers list for autocomplete
     */
    public function getSuppliers(Request $request)
    {
        $term = $request->q ?? '';
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $suppliers = DB::table('contacts')
            ->where('business_id', $business_id)
            ->where('type', 'supplier')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('supplier_business_name', 'like', '%' . $term . '%')
                    ->orWhere('contact_id', 'like', '%' . $term . '%');
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($suppliers as $supplier) {
            $results[] = [
                'id' => $supplier->id,
                'text' => $supplier->name,
                'business_name' => $supplier->supplier_business_name ?? '',
                'contact_id' => $supplier->contact_id ?? '',
            ];
        }

        return response()->json($results);
    }

    /**
     * Display list of shipments.
     *
     * @return \Illuminate\Http\Response
     */
    public function shipments()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('purchase::shipments')->with(compact('shipping_statuses'));
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

        $business_id = request()->session()->get('user.business_id') ?? 1;

        $transaction = Transaction::where('business_id', $business_id)
            ->findOrFail($id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('purchase::partials.edit_shipping')
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
                    $contact = Contact::find($transaction_obj->contact_id);
                    Shipment::create([
                        'business_id' => $business_id,
                        'transaction_id' => $id,
                        'shipment_no' => $transaction_obj->ref_no,
                        'vendor' => !empty($contact) ? $contact->name : 'Supplier',
                        'vendor_country' => !empty($contact) ? ($contact->country ?? 'Unknown') : 'Unknown',
                        'incoterms' => 'CIF',
                        'port_of_loading' => 'Foreign',
                        'port_of_discharge' => 'Local',
                        'transport_mode' => 'sea',
                        'status' => 'pending',
                        'expected_arrival' => Carbon::parse($transaction_obj->transaction_date)->addDays(30),
                        'user_id' => auth()->id(),
                        'shipping_details' => $transaction_obj->shipping_details,
                        'shipping_address' => $transaction_obj->shipping_address,
                        'shipping_status' => $transaction_obj->shipping_status,
                        'delivered_to' => $transaction_obj->delivered_to,
                        'shipping_charges' => $transaction_obj->shipping_charges,
                    ]);
                }
            }

            $output = [
                'success' => 1,
                'msg' => __("lang_v1.updated_success")
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

    public function getProductsPurchases()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id') ?? 1;
            $term = request()->term;
            $module = request()->module ?? null;

            $supplier_id=request()->supplier_id;
         
            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $suppliermapped = SupplierProductMapping::where('supplier_product_mappings.supplier_id', $supplier_id)->get();
         
            if(!empty(request()->supplier_id) && $suppliermapped->count() > 0)
            {
                        $q = Product::leftJoin(
                            'variations',
                            'products.id',
                            '=',
                            'variations.product_id'
                        )
                          ->leftJoin('supplier_product_mappings', 'products.id', '=', 'supplier_product_mappings.product_id')
                           ->where('supplier_product_mappings.supplier_id', '=', $supplier_id)
                           ->where(function ($query) use ($term) {
                                $query->where('products.name', 'like', '%' . $term . '%');
                                $query->orWhere('sku', 'like', '%' . $term . '%');
                                $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                            })
                           ->active()
                            ->where('business_id', $business_id)
                            ->whereNull('variations.deleted_at')
                            ->select(
                                'products.id as product_id',
                                'products.name',
                                'products.type',
                                // 'products.sku as sku',
                                'variations.id as variation_id',
                                'variations.name as variation',
                                'variations.sub_sku as sub_sku'
                            )
                            ->groupBy('variation_id');
            }
            else
            {
                $q = Product::leftJoin(
                            'variations',
                            'products.id',
                            '=',
                            'variations.product_id'
                        )
                           
                            ->where(function ($query) use ($term) {
                                $query->where('products.name', 'like', '%' . $term . '%');
                                $query->orWhere('sku', 'like', '%' . $term . '%');
                                $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                            })
                            ->active()
                            ->where('business_id', $business_id)
                            ->whereNull('variations.deleted_at')
                            ->select(
                                'products.id as product_id',
                                'products.name',
                                'products.type',
                                // 'products.sku as sku',
                                'variations.id as variation_id',
                                'variations.name as variation',
                                'variations.sub_sku as sub_sku'
                            )
                            ->groupBy('variation_id');
            }
            
            if(!empty($module)){
                $q->forModule($module);
            }
            
            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();

            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku
                    ];
            }
    
            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single') {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key,
                            'sku' => $value['name']
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            if($variation['variation_name'] != 'DUMMY'){
                                $text = $text . ' (' . $variation['variation_name'] . ')';
                            }
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                        ];
                    }
                    $i++;
                }
            }
    
            return response()->json($result);
        }
    }

    public function destroy($id)
    {
        // Re-implementing destroy for completeness
        try {
            $business_id = request()->session()->get('user.business_id') ?? 1;

            $transaction = Transaction::where('id', $id)
                ->where('business_id', $business_id)
                ->with(['purchase_lines'])
                ->firstOrFail();

            DB::beginTransaction();

            foreach ($transaction->purchase_lines as $purchase_line) {
                // Adjust stock (reversing purchase)
                $this->productUtil->updateProductStock('received', $transaction, $purchase_line->product_id, $purchase_line->variation_id, 0, $purchase_line->quantity, null, $transaction->store_id);
                $purchase_line->delete();
            }

            TransactionPayment::where('transaction_id', $id)->delete();
            AccountTransaction::where('transaction_id', $id)->delete();

            $transaction->delete();

            DB::commit();

            return redirect()->route('purchase.index')->with('status', ['success' => 1, 'msg' => __('Purchase deleted successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in destroying purchase: ' . $e->getMessage());
            return redirect()->back()->with('status', ['success' => 0, 'msg' => __('messages.something_went_wrong')]);
        }
    }

    public function bulkImport()
    {
        return view('purchase::bulk_import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Invoice No.',
            'Supplier Name',
            'Supplier Phone Number',
            'Supplier Email',
            'Sale Date',
            'Product Name',
            'Product SKU',
            'Quantity',
            'Product Unit',
            'Unit Price',
            'Item Tax',
            'Item Discount',
            'Item Description',
            'Order Total'
        ];

        $filename = "bulk_purchase_template.csv";
        
        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function bulkImportPost(Request $request)
    {
        try {
            if ($request->hasFile('bulk_purchase_csv')) {
                $file = $request->file('bulk_purchase_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1); // Remove header
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                DB::beginTransaction();
                
                // Group by Invoice No. (index 0)
                $grouped_data = [];
                foreach ($imported_data as $row) {
                    if (empty($row[0])) continue;
                    $invoice_no = $row[0];
                    $grouped_data[$invoice_no][] = $row;
                }

                $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

                foreach ($grouped_data as $invoice_no => $rows) {
                    $first_row = $rows[0];
                    
                    // Find or create contact
                    $contact = null;
                    if (!empty($first_row[2])) { // mobile
                        $contact = Contact::where('business_id', $business_id)->where('mobile', $first_row[2])->first();
                    } elseif (!empty($first_row[3])) { // email
                        $contact = Contact::where('business_id', $business_id)->where('email', $first_row[3])->first();
                    }

                    if (empty($contact)) {
                        $contact = Contact::create([
                            'business_id' => $business_id,
                            'type' => 'supplier',
                            'name' => $first_row[1] ?? ($first_row[2] ?? 'Bulk Supplier'),
                            'mobile' => $first_row[2] ?? null,
                            'email' => $first_row[3] ?? null,
                            'created_by' => $user_id
                        ]);
                    }

                    $location = BusinessLocation::where('business_id', $business_id)->first();
                    $store = Store::where('business_id', $business_id)->first();

                    $transaction_data = [
                        'business_id' => $business_id,
                        'type' => 'purchase',
                        'status' => 'received',
                        'payment_status' => 'due',
                        'invoice_no' => $invoice_no,
                        'ref_no' => $invoice_no,
                        'contact_id' => $contact->id,
                        'transaction_date' => !empty($first_row[4]) ? Carbon::parse($first_row[4])->toDateTimeString() : Carbon::now()->toDateTimeString(),
                        'total_before_tax' => $this->productUtil->num_uf($first_row[13] ?? 0, $currency_details),
                        'final_total' => $this->productUtil->num_uf($first_row[13] ?? 0, $currency_details),
                        'created_by' => $user_id,
                        'location_id' => $location ? $location->id : 1,
                        'store_id' => $store ? $store->id : 1,
                    ];

                    $transaction = Transaction::create($transaction_data);

                    $purchase_lines = [];
                    foreach ($rows as $row) {
                        $variation = null;
                        $product = null;
                        if (!empty($row[6])) { // Product SKU
                            $variation = Variation::where('sub_sku', $row[6])->first();
                            $product = $variation ? Product::find($variation->product_id) : null;
                        } else {
                            $product = Product::where('business_id', $business_id)->where('name', $row[5])->first();
                            $variation = $product ? Variation::where('product_id', $product->id)->first() : null;
                        }

                        if ($variation) {
                            $quantity = $this->productUtil->num_uf($row[7] ?? 0, $currency_details);
                            $unit_price = $this->productUtil->num_uf($row[9] ?? 0, $currency_details);
                            
                            $tax_id = null;
                            $item_tax = 0;
                            $price_before_tax = $unit_price;

                            if (!empty($row[10])) { // Item Tax (name)
                                $tax = TaxRate::where('business_id', $business_id)
                                    ->where('name', trim($row[10]))
                                    ->first();
                                if ($tax) {
                                    $tax_id = $tax->id;
                                    $price_before_tax = ($unit_price * 100) / (100 + $tax->amount);
                                    $item_tax = $unit_price - $price_before_tax;
                                }
                            }

                            $item_discount = $this->productUtil->num_uf($row[11] ?? 0, $currency_details);

                            $purchase_lines[] = [
                                'product_id' => $variation->product_id,
                                'variation_id' => $variation->id,
                                'quantity' => $quantity,
                                'pp_without_discount' => $price_before_tax + $item_discount,
                                'purchase_price' => $price_before_tax,
                                'purchase_price_inc_tax' => $unit_price,
                                'item_tax' => $item_tax,
                                'tax_id' => $tax_id,
                                'discount_percent' => 0,
                                'product_unit_id' => $product->unit_id,
                                'purchase_line_note' => $row[12] ?? null // Item Description
                            ];

                            // Handle Unit if specified
                            if (!empty($row[8])) { // Product Unit
                                $unit_name = trim($row[8]);
                                $unit = Unit::where('actual_name', $unit_name)
                                    ->orWhere('short_name', $unit_name)
                                    ->first();
                                if ($unit) {
                                    $purchase_lines[count($purchase_lines)-1]['sub_unit_id'] = $unit->id;
                                }
                            }
                        }
                    }

                    $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchase_lines, $currency_details, false, $transaction->store_id);
                    
                    // Update stock
                    foreach ($purchase_lines as $line) {
                        $this->productUtil->updateProductStock('received', $transaction, $line['product_id'], $line['variation_id'], $line['quantity'], 0, $currency_details, $transaction->store_id);
                    }
                }

                DB::commit();
                return redirect()->route('purchase.index')->with('status', ['success' => 1, 'msg' => __('Bulk Purchase Imported successfully')]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }
}

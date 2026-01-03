<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessLocation;
use Modules\Contacts\Models\Contact;
use App\Models\TaxRate;
use App\Category;
use App\Brands;
use App\Account;
use App\Models\AccountTransaction;
use Modules\Contacts\Models\ContactGroup;
use App\Transaction;
use App\TransactionPayment;
use App\Store;
use App\Variation;
use App\TransactionSellLine;
use App\Product;
use App\Business;
use App\Models\User;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ContactUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\SellingPriceGroup;
use App\TypesOfService;

class PosController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $businessUtil;
    protected $moduleUtil;
    protected $contactUtil;
    protected $cashRegisterUtil;
    protected $notificationUtil;
    protected $dummyPaymentLine;

    public function __construct(
        ProductUtil $productUtil,
        TransactionUtil $transactionUtil,
        BusinessUtil $businessUtil,
        ModuleUtil $moduleUtil,
        ContactUtil $contactUtil,
        CashRegisterUtil $cashRegisterUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->contactUtil = $contactUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->notificationUtil = $notificationUtil;

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
        return view('sales::pos.index');
    }

    /**
     * DataTables list for POS sales.
     */
    public function list(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        $sales = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.is_pos', 1)
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id')
            ->select([
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'contacts.name as customer_name',
                'contacts.is_default as is_walk_in',
                'business_locations.name as location_name',
                'transactions.payment_status',
                'transactions.final_total',
                'transactions.status as status',
                DB::raw('(SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments AS tp WHERE tp.transaction_id=transactions.id) as total_paid')
            ]);

        return datatables()->of($sales)
            ->addColumn('action', function ($row) {
                $html = '<div class="dropdown dropdown-action">
                            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('sales.show', $row->id) . '">
                                    <i class="fa-solid fa-eye m-r-5"></i> ' . __('View') . '
                                </a>
                                <a class="dropdown-item pointer" onclick="pos_print_invoice(' . $row->id . ')">
                                    <i class="fa-solid fa-print m-r-5"></i> ' . __('Print') . '
                                </a>
                                <a class="dropdown-item" href="' . route('sales.edit', $row->id) . '">
                                    <i class="fa-solid fa-pencil m-r-5"></i> ' . __('Edit') . '
                                </a>
                                <form action="' . route('sales.destroy', $row->id) . '" method="POST" onsubmit="return confirm(\'' . __('Are you sure?') . '\');" style="display:inline">
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
            ->rawColumns(['action', 'customer_name', 'final_total', 'total_paid', 'total_due', 'payment_status', 'transaction_date'])
            ->make(true);
    }

    /**
     * Show the POS screen.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        if (!auth()->user()->can('sell.create')) {
            // abort(403, 'Unauthorized action.');
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->route('sales.cash-register.create');
        }

        $register_details = $this->cashRegisterUtil->getCurrentCashRegister(auth()->user()->id);
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        $business_details = $this->businessUtil->getDetails($business_id);
        
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        $customers = Contact::customersDropdown($business_id, false);
        $customer_groups = ContactGroup::forDropdown($business_id);
        
        $taxes = TaxRate::where('business_id', $business_id)->get();
        
        $default_location = BusinessLocation::findOrFail($register_details->location_id);
        $payment_types = $this->productUtil->payment_types($default_location, false, false, false, true, "is_sale_enabled");
        
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::where('business_id', $business_id)->pluck('name', 'id');
        
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        $default_location_id = $register_details->location_id;
        $default_store_id = Store::where('location_id', $default_location_id)->first()->id ?? null;

        $total_outstanding = 0;
        if (!empty($walk_in_customer)) {
            $total_outstanding = $this->contactUtil->getCustomerBalance($walk_in_customer['id'], $business_id, true);
        }

        return view('sales::pos.create', compact(
            'business_locations',
            'default_location',
            'default_location_id',
            'default_store_id',
            'customers',
            'customer_groups',
            'taxes',
            'payment_types',
            'accounts',
            'categories',
            'brands',
            'pos_settings',
            'shortcuts',
            'price_groups',
            'types_of_service',
            'walk_in_customer',
            'total_outstanding'
        ));
    }

    /**
     * Show the POS edit screen.
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        if (!auth()->user()->can('sell.update')) {
             abort(403, 'Unauthorized action.');
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->route('sales.cash-register.create');
        }

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with(['sell_lines', 'sell_lines.product', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'contact'])
            ->findOrFail($id);

        //Check if transaction can be edited
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return redirect()->route('sales.pos.index')
                ->with('status', [
                    'success' => 0,
                    'msg' => __('Transaction edit not allowed'),
                ]);
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        $customers = Contact::customersDropdown($business_id, false);
        $customer_groups = ContactGroup::forDropdown($business_id);
        
        $taxes = TaxRate::where('business_id', $business_id)->get();
        
        $default_location = BusinessLocation::findOrFail($transaction->location_id);
        $payment_types = $this->productUtil->payment_types($default_location, false, false, false, true, "is_sale_enabled");
        
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::where('business_id', $business_id)->pluck('name', 'id');
        
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        $default_location_id = $transaction->location_id;
        $default_store_id = $transaction->store_id;

        $total_outstanding = $this->contactUtil->getCustomerBalance($transaction->contact_id, $business_id, true);

        return view('sales::pos.create', compact(
            'transaction',
            'business_locations',
            'default_location',
            'default_location_id',
            'default_store_id',
            'customers',
            'customer_groups',
            'taxes',
            'payment_types',
            'accounts',
            'categories',
            'brands',
            'pos_settings',
            'shortcuts',
            'price_groups',
            'types_of_service',
            'total_outstanding'
        ));
    }

    /**
     * Store a POS sale.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $user_id = $request->session()->get('user.id') ?? 1;

            $input = $request->all();

            // Check if there is a open register
            if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
                return response()->json([
                    'success' => 0,
                    'msg' => __('Please open a cash register first')
                ]);
            }

            DB::beginTransaction();

            $input['transaction_date'] = !empty($input['transaction_date']) ? $input['transaction_date'] : Carbon::now()->toDateTimeString();
            $input['is_direct_sale'] = 0;
            $input['is_pos'] = 1;

            // Handle Walk-in Customer if contact_id is empty
            $contact_id = $request->get('contact_id', null);
            if (empty($contact_id)) {
                $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
                $contact_id = $walk_in_customer['id'] ?? null;
            }
            $input['contact_id'] = $contact_id;

            // Customer group details
            $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
            $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

            // Invoice total calculation
            $discount = [
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'discount_amount' => $input['discount_amount'] ?? 0,
            ];
            $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'] ?? null, $discount);

            // Determine credit sale
            $is_credit_sale = 0;
            if (!empty($input['payment'])) {
                if ($input['payment'][0]['method'] == 'credit_sale') {
                    $is_credit_sale = 1;
                }
            }
            $input['is_credit_sale'] = $is_credit_sale;

            // Handle Suspend/Draft/Quotation status
            $input['is_suspend'] = 0;
            $input['is_quotation'] = 0;
            if ($input['status'] == 'draft') {
                $input['is_suspend'] = 1;
            } elseif ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_suspend'] = 1;
                $input['is_quotation'] = 1;
            }

            // Create transaction
            $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

            // Create sell lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

            // Accounting for Credit Sale
            if ($is_credit_sale) {
                  $account = Account::where('business_id', $business_id)->where('name', 'Accounts Receivable')->first();
                  $acc_id = $account ? $account->id : null;
                  
                  if($acc_id) {
                        // Create AR transaction
                        $this->transactionUtil->createAccountTransaction($transaction, 'debit', $acc_id);
                        
                        // Create Contact Ledger
                        $this->createContactLedger($transaction, 'debit');
                  }
            }

            // Update product stock only if NOT suspended
            if ($transaction->status == 'final') {
                foreach ($input['products'] as $product) {
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
            }

            // Create payment lines
            if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
            }

            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            // Add payments to Cash Register
            if ($transaction->status == 'final' && !empty($input['payment']) && !$transaction->is_suspend && !$is_credit_sale) {
                $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
            }

            // Calculate and update VAT
            $this->transactionUtil->calculateAndUpdateVAT($transaction);

            // FIFO mapping
            if ($transaction->status == 'final') {
                $business_details = $this->businessUtil->getDetails($business_id);
                $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
                $business_data = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $input['location_id'],
                    'pos_settings' => $pos_settings
                ];
                $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            }

            DB::commit();

            $msg = __('Sale created successfully');
            if ($transaction->is_quotation) {
                $msg = __('Quotation created successfully');
            } elseif ($transaction->is_suspend) {
                $msg = __('Sale suspended successfully');
            }

            $receipt = '';
            if ($transaction->status == 'final' && !$transaction->is_suspend) {
                $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
            }

            return response()->json([
                'success' => 1,
                'msg' => $msg,
                'transaction_id' => $transaction->id,
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Error: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json([
                'success' => 0,
                'msg' => __('Something went wrong') . ': ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update a POS sale.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $user_id = $request->session()->get('user.id') ?? 1;

            $input = $request->all();

            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->findOrFail($id);
            
            $status_before = $transaction->status;

            DB::beginTransaction();

            $input['transaction_date'] = !empty($input['transaction_date']) ? $input['transaction_date'] : Carbon::now()->toDateTimeString();
            
            // Handle Suspend/Draft/Quotation status
            $input['is_suspend'] = 0;
            $input['is_quotation'] = 0;
            if ($input['status'] == 'draft') {
                $input['is_suspend'] = 1;
            } elseif ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_suspend'] = 1;
                $input['is_quotation'] = 1;
            }

            // Customer group details
            $contact_id = $request->get('contact_id', null);
            if (empty($contact_id)) {
                $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
                $contact_id = $walk_in_customer['id'] ?? null;
            }
            $input['contact_id'] = $contact_id;
            
            $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
            $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

            // Invoice total calculation
            $discount = [
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'discount_amount' => $input['discount_amount'] ?? 0,
            ];
            $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'] ?? null, $discount);

            // Update transaction
            $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

            // Update sell lines and handle stock adjustments
            $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);

            // Determine credit sale
            $is_credit_sale = 0;
            if (!empty($input['payment'])) {
                if ($input['payment'][0]['method'] == 'credit_sale') {
                    $is_credit_sale = 1;
                }
            }

            // Update payment lines
            if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                // For POS, we often replace existing payments in edit mode if it was suspended
                // For simplicity here, we clear and re-create if it wasn't already paid
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                
                // Update cash register
                $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
            }

            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            // Calculate and update VAT
            $this->transactionUtil->calculateAndUpdateVAT($transaction);

            // FIFO mapping adjustment
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
            $business_data = [
                'id' => $business_id,
                'accounting_method' => $request->session()->get('business.accounting_method'),
                'location_id' => $transaction->location_id,
                'pos_settings' => $pos_settings
            ];
            $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business_data, $deleted_lines);

            DB::commit();

            $msg = __('Sale updated successfully');
            
            $receipt = '';
            if ($transaction->status == 'final' && !$transaction->is_suspend) {
                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction->id);
            }

            return response()->json([
                'success' => 1,
                'msg' => $msg,
                'transaction_id' => $transaction->id,
                'receipt' => $receipt
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Update Error: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json([
                'success' => 0,
                'msg' => __('Something went wrong') . ': ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Returns a new payment row.
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id') ?? 1;
        $row_index = $request->input('row_index', 0);
        $removable = true;

        $register_details = $this->cashRegisterUtil->getCurrentCashRegister(auth()->user()->id);
        $default_location = BusinessLocation::findOrFail($register_details->location_id);
        $payment_types = $this->productUtil->payment_types($default_location, false, false, false, true, "is_sale_enabled");
        
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        return view('sales::pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'accounts'));
    }

    /**
     * Quick add contact.
     */
    public function quickAddContact(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $user_id = $request->session()->get('user.id') ?? 1;

            $input = $request->only(['name', 'mobile', 'type']);
            $input['business_id'] = $business_id;
            $input['created_by'] = $user_id;
            $input['contact_id'] = $this->contactUtil->generateReferenceNumber('customer', $business_id);

            $contact = Contact::create($input);

            return response()->json([
                'success' => 1,
                'msg' => __('Customer added successfully'),
                'contact_id' => $contact->id,
                'contact_name' => $contact->name
            ]);
        } catch (\Exception $e) {
            Log::error('Quick Add Contact Error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'msg' => __('Something went wrong')
            ]);
        }
    }

    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true
    ) {
        $output = [
            'is_enabled'     => false,
            'print_type'     => 'browser',
            'html_content'   => null,
            'printer_config' => [],
            'data'           => [],
        ];
        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        $output['is_enabled'] = true;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $location_details->invoice_layout_id);
        
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;
        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
        
        $currency_details = [
            'symbol'             => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator'  => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        if ($receipt_printer_type == 'printer') {
            $output['print_type']     = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['printer_config']['design'] = $receipt_details->design;
            $output['data']           = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'sales::pos.receipts.' . $receipt_details->design : 'sales::pos.receipts.classic';
            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }

        return $output;
    }

    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id') ?? 1;
        $user_id = auth()->user()->id;
        $query = Transaction::where('business_id', $business_id)
            ->where('created_by', $user_id)
            ->where('type', 'sell')
            ->orderBy('id', 'desc')
            ->limit(10);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'final');
        }

        $transactions = $query->get();

        return view('sales::pos.partials.recent_transactions', compact('transactions'));
    }

    public function printInvoice(Request $request, $transaction_id)
    {
        if ($request->ajax()) {
            try {
                $business_id = $request->session()->get('user.business_id') ?? 1;
                $transaction = Transaction::where('business_id', $business_id)
                    ->findOrFail($transaction_id);

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id);

                return response()->json(['success' => 1, 'receipt' => $receipt]);
            } catch (\Exception $e) {
                Log::error('Print POS Invoice Error: ' . $e->getMessage());
                return response()->json(['success' => 0, 'msg' => __('Something went wrong')]);
            }
        }
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
        \App\Models\ContactLedger::createContactLedger($account_transaction_data);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->where('type', 'sell')
                    ->with(['sell_lines'])
                    ->first();

                if (!$transaction) {
                    return ['success' => 0, 'msg' => __('messages.something_went_wrong')];
                }

                DB::beginTransaction();

                // Delete sell lines and adjust stock
                foreach ($transaction->sell_lines as $sell_line) {
                    if ($transaction->status == 'final') {
                         $this->productUtil->updateProductQuantity($transaction->location_id, $sell_line->product_id, $sell_line->variation_id, $sell_line->quantity, 0, 'increase', $transaction->store_id);
                         $this->productUtil->updateProductQuantityStore($transaction->location_id, $sell_line->product_id, $sell_line->variation_id, $sell_line->quantity, $transaction->store_id, 'increase');
                    }
                }

                $transaction->delete();
                
                DB::commit();

                return ['success' => 1, 'msg' => __('Sale deleted successfully')];
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Delete POS Error: ' . $e->getMessage());
                return ['success' => 0, 'msg' => __('Something went wrong')];
            }
        }
    }

    /**
     * Get product row for POS cart.
     */
    public function getProductRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $variation_id = $request->input('variation_id');
        $location_id = $request->input('location_id');
        $store_id = $request->input('store_id');

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
            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $variation->product_id);
        }

        return view('sales::pos.partials.product_row', compact('product', 'variation', 'taxes', 'sub_units'));
    }

    /**
     * Get product suggestions for POS search.
     */
    public function getProductSuggestion(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $term = $request->input('term');
        $location_id = $request->input('location_id');
        $store_id = $request->input('store_id');

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
            null,
            false,
            20,
            null
        );
        
        Log::info('POS getProductSuggestion Result Count: ' . count($products));
        
        return response()->json($products);
    }

    /**
     * Get products for POS grid.
     */
    public function getProducts(Request $request)
    {
        $business_id = $request->session()->get('user.business_id') ?? 1;
        $location_id = $request->get('location_id');
        $brand_id = $request->get('brand_id');
        $term = $request->get('term', '');
        $store_id = $request->get('store_id') ?? $request->session()->get('business.default_store');

        $category_id = $request->get('category_id');
        $per_page = $request->get('per_page', 24);

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
            $brand_id,
            $category_id,
            true,
            $per_page,
            null
        );
        
        return response()->json($products);
    }

    public function getCustomerDueDetails(Request $request) {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $contact_id = $request->contact_id;
        
        $contact = Contact::where('business_id', $business_id)
                ->where('id', $contact_id)
                ->first();
        
        if (!$contact) {
            return json_encode(["name" => "", "due" => 0]);
        }
        
        $due = $this->contactUtil->getCustomerBalance($contact_id, $business_id, true);
        
        return json_encode([
            "name" => $contact->name, 
            "due" => $this->transactionUtil->num_f($due)
        ]);
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
}

<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use Modules\Contacts\Models\ContactGroup;
use Modules\Accounting\Models\Account;
use App\Models\BusinessLocation;
use App\Models\TaxRate;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Contacts\Models\Transaction;
use Modules\Accounting\Models\AccountTransaction;
use Carbon\Carbon;
use Modules\Contacts\Models\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;

    public function __construct(ProductUtil $productUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil)
    {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        // ================= AJAX (DataTable) =================
        if (request()->ajax()) {
            $purchases = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with('contact')
                ->select(
                    'transactions.*',
                    DB::raw('COALESCE((SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.deleted_at IS NULL), 0) as amount_paid')
                );

            return DataTables::of($purchases)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="btn-group">
                        <a href="'.route('purchase.show',$row->id).'" class="btn btn-xs btn-info">View</a>
                        <a href="'.route('purchase.edit',$row->id).'" class="btn btn-xs btn-warning">Edit</a>
                    </div>';
                })
                ->addColumn('supplier', function ($row) {
                    return $row->contact->name ?? '';
                })
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    return number_format($due, 2);
                })
                ->editColumn('transaction_date', function($row) {
                    return \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d');
                })
                ->editColumn('status', function($row) {
                    $status_color = $row->status == 'received' ? 'success' : ($row->status == 'pending' ? 'warning' : 'danger');
                    return '<span class="badge bg-inverse-' . $status_color . '">' . ucfirst($row->status) . '</span>';
                })
                 ->editColumn('payment_status', function($row) {
                    return '<span class="badge bg-primary">' . ucfirst($row->payment_status) . '</span>';
                })
                ->editColumn('final_total', function($row) {
                    return number_format($row->final_total, 2);
                })
                ->rawColumns(['action', 'status', 'payment_status', 'payment_due', 'final_total'])
                ->make(true);
        }

        // ================= NORMAL PAGE LOAD (GRID VIEW) =================
        $purchases = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->with('contact')
            ->latest()
            ->get();

        return view('purchase::index', compact('purchases'));
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

        $bank_group_accounts = Account::leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
            ->where('accounts.business_id', $business_id)
            ->where('account_groups.name', 'Bank Account')
            ->pluck('accounts.name', 'accounts.id');
            
        $cpc_accounts = Account::leftjoin('account_groups', 'accounts.asset_type', 'account_groups.id')
            ->where('accounts.business_id', $business_id)
            ->where('account_groups.name', 'CPC')
            ->pluck('accounts.name', 'accounts.id');

        $purchase_no = $this->businessUtil->getFormNumber('purchase');

        $payment_types =  $this->productUtil->payment_types(null, true, true, false, false, true);
        
        $accounts = Account::where('business_id', $business_id)->pluck('name', 'id');

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
            'cpc_accounts'
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
                'location_id', 'discount_amount', 'tax_id', 'tax_amount',
                'final_total', 'additional_notes', 'pay_term_number', 'pay_term_type',
                'invoice_no', 'invoice_date', 'is_vat'
            ]);

            $exchange_rate = 1;

            // Unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;
            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'] ?? 0, $currency_details) * $exchange_rate;
            $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'] ?? 0, $currency_details) * $exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['store_id'] = $request->input('store_id');
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            $transaction_data['exchange_rate'] = $exchange_rate;

            // Upload document
            $transaction_data['document'] = $this->productUtil->uploadFile($request, 'document', 'documents');

            // Update reference count and generate reference number
            $ref_count = $this->productUtil->setAndGetReferenceCount('purchase');
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber('purchase', $ref_count);
            }

            $taxes = TaxRate::where('business_id', $business_id)->first();
            $tax_id = !empty($taxes) ? $taxes->id : 1;
            $transaction_data['tax_id'] = $tax_id;

            $transaction = Transaction::create($transaction_data);

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
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $payments, $business_id, $user_id);
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
        return view('purchase::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('purchase::edit');
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
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $product_id = $request->product_id;
        $variation_id = $request->variation_id ?? 0;
        $row_count = $request->row_count ?? 0;
        $location_id = $request->location_id;

        // Get product with variations and tax
        $product = Product::with(['variations', 'variations.product_variation', 'unit', 'tax'])
            ->findOrFail($product_id);

        // Get variation if specified
        $variation = null;
        if ($variation_id) {
            $variation = $product->variations->where('id', $variation_id)->first();
        } else {
            $variation = $product->variations->first();
        }

        // Get current stock for this variation at this location
        $current_stock = $this->productUtil->getProductStockAtLocation($variation->id, $location_id);

        // Get default purchase price
        $default_purchase_price = $variation->dpp_inc_tax ?? 0;
        $default_purchase_price_exc_tax = $variation->default_purchase_price ?? 0;

        // Get taxes
        $taxes = TaxRate::where('business_id', $business_id)->get();

        $enable_lot_number = session('business.enable_lot_number') ?? 0;
        $enable_product_expiry = session('business.enable_product_expiry') ?? 0;

        return view('purchase::partials.purchase_entry_row', compact(
            'product',
            'variation',
            'row_count',
            'current_stock',
            'default_purchase_price',
            'default_purchase_price_exc_tax',
            'taxes',
            'enable_lot_number',
            'enable_product_expiry'
        ));
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}

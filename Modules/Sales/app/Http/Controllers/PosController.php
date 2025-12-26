<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use App\Models\Store;
use App\Models\Variation;
use App\Models\TransactionSellLine;
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
     * Show the POS screen.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;

        if (!auth()->user()->can('sell.create')) {
            // abort(403, 'Unauthorized action.');
        }

        // Check for open register
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
             return redirect()->route('sales.cash-register.create');
        }

        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        $customers = Contact::customersDropdown($business_id, false);
        $customer_groups = ContactGroup::forDropdown($business_id);
        
        $taxes = TaxRate::where('business_id', $business_id)->get();
        
        $payment_types = $this->productUtil->payment_types(null, true, false, false, false, true, "is_sale_enabled");
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::where('business_id', $business_id)->pluck('name', 'id');
        $default_store_id = Store::where('business_id', $business_id)->first()->id ?? null;

        $default_location_id = BusinessLocation::where('business_id', $business_id)->first()->id ?? null;

        return view('sales::pos.create', compact(
            'business_locations',
            'default_location_id',
            'default_store_id',
            'customers',
            'customer_groups',
            'taxes',
            'payment_types',
            'accounts',
            'categories',
            'brands'
        ));
    }

    /**
     * Store a POS sale.
     */
    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id') ?? 1;
            $user_id = $request->session()->get('user.id') ?? 1;

            $input = $request->all();
            
            DB::beginTransaction();

            $transaction_data = $request->only([
                'contact_id', 'transaction_date', 'location_id', 'status', 'is_vat',
                'total_before_tax', 'discount_amount', 'discount_type', 'tax_amount', 'shipping_charges',
                'final_total', 'additional_notes', 'invoice_no'
            ]);

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'sell';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['store_id'] = $request->input('store_id') ?? request()->session()->get('business.default_store');
            
            if (empty($transaction_data['transaction_date'])) {
                $transaction_data['transaction_date'] = Carbon::now();
            }

            if (empty($transaction_data['invoice_no'])) {
                $transaction_data['invoice_no'] = $this->businessUtil->getFormNumber('sell');
            }

            $transaction = Transaction::create($transaction_data);

            // Create sell lines
            $products = $request->input('products', []);
            if (!empty($products)) {
                $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $transaction->location_id);
            }

            // Update product stock
            foreach ($products as $product) {
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

            // Add Payments
            $payments = $request->input('payment', []);
            if (!empty($payments)) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $payments, $business_id, $user_id, true, $transaction->status);
            }

            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            // Add payments to Cash Register
            if ($transaction->status == 'final' && !empty($payments)) {
                $this->cashRegisterUtil->addSellPayments($transaction, $payments);
            }

            // Calculate and update VAT
            $this->transactionUtil->calculateAndUpdateVAT($transaction);

            DB::commit();

            return response()->json([
                'success' => 1,
                'msg' => __('Sale created successfully'),
                'transaction_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'msg' => __('Something went wrong') . ': ' . $e->getMessage()
            ]);
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
        $taxes = TaxRate::where('business_id', $business_id)->get();

        return view('sales::pos.partials.product_row', compact('product', 'variation', 'taxes'));
    }

    /**
     * Get product suggestions for POS search.
     */
    public function getProductSuggestion(Request $request)
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $term = $request->input('term');
        $location_id = $request->input('location_id');

        $products = $this->productUtil->filterProduct($business_id, $term, $location_id, false, null, [], ['name', 'sku', 'sub_sku'], false);
        
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
            null
        );

        return response()->json($products);
    }
}

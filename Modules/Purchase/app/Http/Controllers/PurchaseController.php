<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;

use Modules\Contacts\Models\ContactGroup;
use Modules\Accounting\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    protected $productUtil;
    protected $businessUtil;

    public function __construct(ProductUtil $productUtil, BusinessUtil $businessUtil)
    {
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'cheque_date' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', 'account_id' => ''
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('purchase::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        $orderStatuses = $this->productUtil->orderStatuses();
        
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

        $cash_account_id = Account::getAccountByAccountName('Cash')?->id;

        $purchase_no = $this->businessUtil->getFormNumber('purchase');

        $products = [
        [
            "id" => 1,
            "name" => "Laptop Lenovo ThinkPad",
            "code" => "LP-001",
            "description" => "14-inch business laptop",
            "price" => 45000,
        ],
        [
            "id" => 2,
            "name" => "HP LaserJet Printer",
            "code" => "PR-105",
            "description" => "Monochrome laser printer",
            "price" => 18000,
        ]
        
    ];
        return view('purchase::create', compact('purchase_no','products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ref_no' => [
                    'required',
                    Rule::unique('transactions', 'ref_no')
                        ->where(fn ($q) => $q->where('contact_id', $request->contact_id)),
                ],
/*                 'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'invoice_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'store_id' => 'required', */
                'document' => 'nullable|file|max:' . (config('constants.document_size_limit') / 1000),
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $business_id = session()->get('user.business_id') ?? 1;

            $transaction_data = $request->only([
                'is_vat', 'invoice_no', 'invoice_date', 'ref_no', 'status',
                'contact_id', 'transaction_date', 'total_before_tax',
                'location_id', 'discount_amount', 'tax_id', 'tax_amount',
                'final_total', 'additional_notes',
                'pay_term_number', 'pay_term_type'
            ]);

            $exchange_rate = 1; // $this->businessUtil->getExchangeRate($business_id);

            $user_id = $request->session()->get('user.id');
            //$enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            //Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

            $currency_details = 1; // $this->businessUtil->getCurrencyDetails($business_id);

/*             $taxes = TaxRate::where('business_id', $business_id)->first();
            $tax_id = !empty($taxes) ? $taxes->id : 1; */
            $transaction_data['tax_id'] = 1;

            //input values
/*             $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

            $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details) * $exchange_rate;

            $transaction_data['tax_amount'] = 1;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;
            */
            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['store_id'] = $request->input('store_id');
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            
            //upload document
            $transaction_data['document'] = $this->productUtil->uploadFile($request, 'document', 'documents');

            // continue saving logic...
            DB::beginTransaction();

            $transaction = Transaction::create($transaction_data);

            $notification = notify(__('Purchase has been completed successfully'));
            return back()->with($notification);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Error in storing purchase: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', __('messages.something_went_wrong'))
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}

<?php

namespace Modules\StockAdjustment\Http\Controllers;
use App\Models\BusinessLocation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\DataTables\StockAdjustmentDataTable;
use App\Transaction;
use App\Account;
use App\AccountType;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use DB;

class StockAdjustmentController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    public function __construct(
        ProductUtil $productUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->productUtil     = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil      = $moduleUtil;
    }

    /**
     * Display listing
     */
    public function index(StockAdjustmentDataTable $dataTable)
    {
        $pageTitle = __('Stock Adjustments');
           return $dataTable->render('stockadjustment::index', compact('pageTitle'));
    }

    /**
     * Show create form
     */
    public function create()
{
    if (! auth()->user()->can('purchase.create')) {
        abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');

    // Check subscription
    if (! $this->moduleUtil->isSubscribed($business_id)) {
        return $this->moduleUtil->expiredResponse(
            action([\App\Http\Controllers\StockAdjustmentController::class, 'index'])
        );
    }

    // Generate reference number
    $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
    $ref_no = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

    $business_locations = BusinessLocation::forDropdown($business_id);

    return view('stockadjustment::add_stock_adjustment')
            ->with(compact('business_locations','ref_no'));
}


    /**
     * Store stock adjustment
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $user_id     = $request->session()->get('user.id');

            $data = $request->validate([
                'location_id'            => 'required',
                'transaction_date'       => 'required',
                'adjustment_type'        => 'required',
                'stock_adjustment_type'  => 'required',
                'final_total'            => 'required|numeric',
                'total_amount_recovered' => 'nullable|numeric',
                'additional_notes'       => 'nullable|string',
            ]);

            $data['type']          = 'stock_adjustment';
            $data['business_id']   = $business_id;
            $data['created_by']    = $user_id;
            $data['transaction_date']
                = $this->productUtil->uf_date($data['transaction_date'], true);

            // Reference number
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
            $data['ref_no']
                = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

            Transaction::create($data);

            DB::commit();

            return redirect()
                ->route('stock-adjustments.index')
                ->with('status', [
                    'success' => 1,
                    'msg' => __('Stock adjustment added successfully')
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error($e);

            return redirect()
                ->back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('Something went wrong')
                ]);
        }
    }

    /**
     * AJAX: Get inventory adjustment accounts
     */
    public function getInventoryAdjustmentAccount(Request $request)
    {
        $type = $request->type;
        $business_id = $request->session()->get('user.business_id');

        if ($type === 'increase') {
            $accountType = AccountType::getAccountTypeIdByName('Income', $business_id);
        } else {
            $accountType = AccountType::getAccountTypeIdByName('Expenses', $business_id);
        }

        if (empty($accountType)) {
            return '<option value="">Please Select</option>';
        }

        $accounts = Account::where('account_type_id', $accountType->id)
            ->pluck('name', 'id');

        return $this->transactionUtil
            ->createDropdownHtml($accounts, 'Please Select');
    }
}

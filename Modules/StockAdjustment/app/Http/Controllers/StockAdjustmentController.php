<?php
namespace Modules\StockAdjustment\Http\Controllers;

use App\Models\BusinessLocation;
use App\Account;
use App\Category;
use Modules\Contacts\Models\AccountTransaction;
use App\AccountType;
use App\Business;
use App\Product;
use App\Unit;
use App\PurchaseLine;
use App\Store;
use Modules\Contacts\Models\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Http\Controllers\Controller;
use App\DataTables\StockAdjustmentDataTable;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use DB;
use Spatie\Activitylog\Models\Activity;

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

    public function index(StockAdjustmentDataTable $dataTable)
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        return $dataTable->render('stockadjustment::index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = auth()->user()->business_id;

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(route('stock_adjustment.index'));
        }

        //Update reference count
        $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment', $business_id);
        $ref_no = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);

        $business_locations = BusinessLocation::forDropdown($business_id);
        $stores = Store::where('business_id', $business_id)->pluck('name', 'id');

        
        $transaction_date = \Carbon\Carbon::now()->format('Y-m-d H:i');

        return view('stockadjustment::add_stock_adjustment')
            ->with(compact('business_locations', 'ref_no', 'stores', 'transaction_date'));
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
{
    if (!auth()->user()->can('purchase.create')) {
        abort(403, 'Unauthorized action.');
    }

    try {
        DB::beginTransaction();

        $input_data = $request->only(['location_id', 'transaction_date', 'adjustment_type', 'stock_adjustment_type', 'additional_notes', 'total_amount_recovered', 'final_total', 'ref_no', 'store_id']);
        $business_id = auth()->user()->business_id;

        // Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(route('stock_adjustment.index'));
        }

        $user_id = auth()->user()->id;

        $input_data['type'] = 'stock_adjustment';
        $input_data['business_id'] = $business_id;
        $input_data['created_by'] = $user_id;

        
        try {
            $input_data['transaction_date'] = \Carbon\Carbon::parse($input_data['transaction_date'])->toDateTimeString();
        } catch (\Exception $e) {
            $input_data['transaction_date'] = \Carbon\Carbon::now()->toDateTimeString();
        }
        
        $input_data['total_amount_recovered'] = $this->productUtil->num_uf($input_data['total_amount_recovered']);

        // Update reference count
        $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment', $business_id);
        // Generate reference number
        if (empty($input_data['ref_no'])) {
            $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);
        }

        $products = $request->input('products');
        $stock_adjustment = Transaction::create($input_data);

        if (!empty($products)) {
            $product_data = [];

            foreach ($products as $product) {
                $adjustment_line = [
                    'product_id' => $product['product_id'],
                    'variation_id' => $product['variation_id'],
                    'quantity' => $this->productUtil->num_uf($product['quantity']),
                    'unit_price' => $this->productUtil->num_uf($product['unit_price']),
                    'type' => request()->adjustment_type,
                    'stock_adjustment_type' => request()->stock_adjustment_type,
                ];
                $prod_amount = $adjustment_line['quantity'] * $adjustment_line['unit_price'];

                if (!empty($product['lot_no_line_id'])) {
                    // Add lot_no_line_id to stock adjustment line
                    $adjustment_line['lot_no_line_id'] = $product['lot_no_line_id'];
                }
                $product_data[] = $adjustment_line;

                // Decrease available quantity
                $this->productUtil->decreaseProductQuantity(
                    $product['product_id'],
                    $product['variation_id'],
                    $input_data['location_id'],
                    $this->productUtil->num_uf($product['quantity']),
                    0,
                    $request['stock_adjustment_type']
                );

                $store_id = !empty($input_data['store_id']) ? $input_data['store_id'] : Store::where('business_id', $business_id)->first()->id;
                $this->productUtil->decreaseProductQuantityStore(
                    $product['product_id'],
                    $product['variation_id'],
                    $input_data['location_id'],
                    $this->productUtil->num_uf($product['quantity']),
                    $store_id,
                    $request['stock_adjustment_type'],
                    0
                );
                $this_product = Product::where('id', $product['product_id'])->first();
                $category  = Category::where('id', $this_product->sub_category_id)->first();

                if ($request['stock_adjustment_type']  == 'increase') {
                        if (!empty($this_product->stock_type)) {
                            $account_transaction_data = [
                                'amount' => $prod_amount,
                                'account_id' => $this_product->stock_type,
                                'type' => 'debit',
                                'operation_date' => $stock_adjustment->transaction_date,
                                'created_by' => $stock_adjustment->created_by,
                                'transaction_id' => $stock_adjustment->id,
                                'transaction_payment_id' => null,
                                'note' => null
                            ];

                            AccountTransaction::createAccountTransaction($account_transaction_data);
                        }

                        if (!empty($category->price_increment_acc)) {
                            $account_transaction_data = [
                                'amount' => $prod_amount,
                                'account_id' => $category->price_increment_acc,
                                'type' => 'credit',
                                'operation_date' => $stock_adjustment->transaction_date,
                                'created_by' => $stock_adjustment->created_by,
                                'transaction_id' => $stock_adjustment->id,
                                'transaction_payment_id' => null,
                                'note' => null
                            ];

                            AccountTransaction::createAccountTransaction($account_transaction_data);
                        }
                    }
                    if ($request['stock_adjustment_type']  ==  'decrease') {
                        if (!empty($this_product->stock_type)) {

                            $account_transaction_data = [
                                'amount' => $prod_amount,
                                'account_id' => $this_product->stock_type,
                                'type' => 'credit',
                                'operation_date' => $stock_adjustment->transaction_date,
                                'created_by' => $stock_adjustment->created_by,
                                'transaction_id' => $stock_adjustment->id,
                                'transaction_payment_id' => null,
                                'note' => null
                            ];

                            AccountTransaction::createAccountTransaction($account_transaction_data);
                        }

                        if (!empty($category->price_reduction_acc)) {
                            $account_transaction_data = [
                                'amount' => $prod_amount,
                                'account_id' => $category->price_reduction_acc,
                                'type' => 'credit',
                                'operation_date' => $stock_adjustment->transaction_date,
                                'created_by' => $stock_adjustment->created_by,
                                'transaction_id' => $stock_adjustment->id,
                                'transaction_payment_id' => null,
                                'note' => null
                            ];

                            AccountTransaction::createAccountTransaction($account_transaction_data);
                        }
                    }
            }

            $stock_adjustment->stock_adjustment_lines()->createMany($product_data);

            // Map Stock adjustment & Purchase.
            $business = [
                'id' => $business_id,
                'accounting_method' => $request->session()->get('business.accounting_method'),
                'location_id' => $input_data['location_id'],
            ];
            $this->transactionUtil->mapPurchaseSell($business, $stock_adjustment->stock_adjustment_lines, 'stock_adjustment');
        }

        DB::commit();

        // Create a success notification
        $notification = notify(__('Stock adjustment added successfully'));

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
        $msg = 'Something went wrong';

        if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
            $msg = $e->getMessage();
        }

        // Create an error notification
        $notification = notify($msg);
    }

    return redirect()->route('stock_adjustment.index')->with($notification);
}
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }
       
        $business_id = auth()->user()->business_id;
        $stock_adjustment = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.id', $id)
            ->where('transactions.type', 'stock_adjustment')
            ->with(['stock_adjustment_lines', 'location', 'business', 'stock_adjustment_lines.variation', 'stock_adjustment_lines.variation.product', 'stock_adjustment_lines.variation.product_variation', 'stock_adjustment_lines.lot_details'])
            ->first();

        $lot_n_exp_enabled = false;
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_n_exp_enabled = true;
        }

        $activities = Activity::forSubject($stock_adjustment)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        return view('stockadjustment::show')
            ->with(compact('stock_adjustment', 'lot_n_exp_enabled', 'activities'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
{
    if (!auth()->user()->can('purchase.delete')) {
        abort(403, 'Unauthorized action.');
    }

    try {
        DB::beginTransaction();

        $stock_adjustment = Transaction::where('id', $id)
            ->where('type', 'stock_adjustment')
            ->with(['stock_adjustment_lines'])
            ->first();

        // Add deleted product quantity to available quantity
        $stock_adjustment_lines = $stock_adjustment->stock_adjustment_lines;
        if (!empty($stock_adjustment_lines)) {
            $line_ids = [];
            foreach ($stock_adjustment_lines as $stock_adjustment_line) {
                $reverse_type = $stock_adjustment->stock_adjustment_type == 'increase' ? 'decrease' : 'increase';

                $this->productUtil->decreaseProductQuantity(
                    $stock_adjustment_line->product_id,
                    $stock_adjustment_line->variation_id,
                    $stock_adjustment->location_id,
                    $stock_adjustment_line->quantity,
                    0,
                    $reverse_type
                );

                $this->productUtil->decreaseProductQuantityStore(
                    $stock_adjustment_line->product_id,
                    $stock_adjustment_line->variation_id,
                    $stock_adjustment->location_id,
                    $stock_adjustment_line->quantity,
                    $stock_adjustment->store_id,
                    $reverse_type
                );

                $line_ids[] = $stock_adjustment_line->id;
            }

            $this->transactionUtil->mapPurchaseQuantityForDeleteStockAdjustment($line_ids);
        }

        // Delete the stock adjustment
        $stock_adjustment->delete();

        // Create a notification message
        $notification = notify(__('Stock adjustment deleted successfully'));
        DB::commit();

        return back()->with($notification);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

        // Create a notification for failure
        $notification = notify(__('Something went wrong'));
        return back()->with($notification);
    }
}

    /**
     * Return inventory adjustment accounts
     */
    public function getInventoryAdjustmentAccount(Request $request)
    {
        $type = $request->type;
        $business_id = auth()->user()->business_id;
        $account_type = null;

        if ($type == 'increase') {
            $account_type = AccountType::getAccountTypeIdByName('Income', $business_id)->id;
        }

        if ($type == 'decrease') {
            $account_type = AccountType::getAccountTypeIdByName('Expenses', $business_id)->id;
        }
        $result = '<option value="">Please Select</option>';
        $account_access = $this->moduleUtil->hasThePermissionInSubscription($business_id, 'access_account');
        if ($account_access == 0) {
            return $result;
        }

        if (!empty($account_type)) {
            $result = Account::where('account_type_id', $account_type)->pluck('name', 'id');
        }
        return $this->transactionUtil->createDropdownHtml($result, 'Please Select');
    }

    public function getProductRow(Request $request)
    {
        if (request()->ajax()) {
            $row_index = $request->input('row_index');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');

            $business_id = auth()->user()->business_id;
            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
            $type = !empty($request->input('type')) ? $request->input('type') : 'stock_adjustment';

            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->id);
            
            return view('stockadjustment::partials.product_table_row')
                ->with(compact('product', 'row_index', 'sub_units'));
        }
    }
}

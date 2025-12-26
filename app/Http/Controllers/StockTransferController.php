<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\VariationStoreDetail;
use App\PurchaseLine;
use App\Store;
use App\Transaction;
use App\TransactionSellLinesPurchaseLines;
use App\Utils\ModuleUtil;
use App\TransactionSellLine;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtil $productUtil
     * @param TransactionUtil $transactionUtil
     * @param ModuleUtil $moduleUtil
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = auth()->user()->business_id;
            $edit_days = auth()->user()->business->transaction_edit_days ?? 30; // Fallback if business not loaded

            $stock_transfers = Transaction::join(
                'business_locations AS l1',
                'transactions.location_id',
                '=',
                'l1.id'
            )
                ->join('transactions as t2', 't2.transfer_parent_id', '=', 'transactions.id')
                ->join(
                    'business_locations AS l2',
                    't2.location_id',
                    '=',
                    'l2.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_transfer')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.ref_no',
                    'l1.name as location_from',
                    'l2.name as location_to',
                    'transactions.From_Account as from_store',
                    'transactions.To_Account as to_store',
                    'transactions.final_total',
                    'transactions.shipping_charges',
                    'transactions.additional_notes',
                    'transactions.id as DT_RowId'
                );

            return DataTables::of($stock_transfers)
                ->addColumn('action', function ($row) use ($edit_days) {
                    $html = '<button type="button" title="' . __("stock_adjustment.view_details") . '" class="btn btn-primary btn-xs view_stock_transfer"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>';
                    $html .= ' <a href="#" class="print-invoice btn btn-info btn-xs" data-href="' . route('stock-transfers.printInvoice', [$row->id]) . '"><i class="fa fa-print" aria-hidden="true"></i> ' . __("messages.print") . '</a>';

                    $date = \Carbon\Carbon::parse($row->transaction_date)->addDays($edit_days);
                    $today = today();
                    if ($date->gte($today)) {
                        $html .= '&nbsp;
                        <button type="button" data-href="' . route("stock-transfers.destroy", [$row->id]) . '" class="btn btn-danger btn-xs delete_stock_transfer"><i class="fa fa-trash" aria-hidden="true"></i> ' . __("messages.delete") . '</button>';
                    }
                    return $html;
                })
                ->removeColumn('id')
                ->removeColumn('shipping_charges')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>'
                )
                ->editColumn(
                    'from_store',
                    function ($row) {
                        $store = Store::where('id', $row->from_store)->select('id', 'name')->first();
                        return $store->name ?? '---';
                    }
                )
                ->editColumn(
                    'to_store',
                    function ($row) {
                        $store = Store::where('id', $row->to_store)->select('id', 'name')->first();
                        return $store->name ?? '---';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->rawColumns(['final_total', 'action'])
                ->make(true);
        }

        $asset_v = 1;
        return view('stock_transfer.index')->with(compact('asset_v'));
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

        $business_locations = BusinessLocation::forDropdown($business_id);
        $default_location = current(array_keys(is_array($business_locations) ? $business_locations : $business_locations->toArray()));

        $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer', $business_id);
        $stock_transfer_form_no = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);

        $asset_v = 1;
        return view('stock_transfer.create')
            ->with(compact('business_locations', 'stock_transfer_form_no', 'default_location', 'asset_v'));
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
            $business_id = auth()->user()->business_id;

            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'ref_no', 'transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);

            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);

            // Check review status if exists in TransactionUtil
            if (method_exists($this->transactionUtil, 'hasReviewed')) {
                $has_reviewed = $this->transactionUtil->hasReviewed($input_data['transaction_date']);
                if (!empty($has_reviewed)) {
                    return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
                }
            }

            $from_store = $request->input('from_store');
            $to_store = $request->input('to_store');
            $user_id = auth()->user()->id;

            $input_data['final_total'] = $this->productUtil->num_uf($input_data['final_total']);
            $input_data['total_before_tax'] = $input_data['final_total'];
            $input_data['type'] = 'sell_transfer';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['To_Account'] = $to_store;
            $input_data['From_Account'] = $from_store;
            $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
            $input_data['status'] = 'final';
            $input_data['payment_status'] = 'paid';

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_transfer', $business_id);
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_transfer', $ref_count);
            }

            $products = $request->input('products');
            $sell_lines = [];
            $purchase_lines = [];

            if (!empty($products)) {
                foreach ($products as $product) {
                    $qty = $this->productUtil->num_uf($product['quantity']);
                    $sell_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $qty,
                        'item_tax' => 0,
                        'tax_id' => null,
                        'unit_price' => $this->productUtil->num_uf($product['unit_price']),
                        'unit_price_inc_tax' => $this->productUtil->num_uf($product['unit_price'])
                    ];

                    $purchase_line_arr = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $qty,
                        'purchase_price' => $sell_line_arr['unit_price'],
                        'purchase_price_inc_tax' => $sell_line_arr['unit_price_inc_tax']
                    ];

                    if (!empty($product['lot_no_line_id'])) {
                        $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];
                        $lot_details = PurchaseLine::find($product['lot_no_line_id']);
                        $purchase_line_arr['lot_number'] = $lot_details->lot_number;
                        $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
                        $purchase_line_arr['exp_date'] = $lot_details->exp_date;
                    }

                    $sell_lines[] = $sell_line_arr;
                    $purchase_lines[] = $purchase_line_arr;
                }
            }

            //Create Sell Transfer transaction
            $input_data['store_id'] = $from_store;
            $sell_transfer = Transaction::create($input_data);

            //Create Purchase Transfer at transfer location
            $input_data['type'] = 'purchase_transfer';
            $input_data['status'] = 'received';
            $input_data['location_id'] = $request->input('transfer_location_id');
            $input_data['transfer_parent_id'] = $sell_transfer->id;
            $input_data['store_id'] = $to_store;
            $purchase_transfer = Transaction::create($input_data);

            //Sell Product from first location
            if (!empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, $sell_transfer->location_id);
            }

            //Purchase product in second location
            if (!empty($purchase_lines)) {
                $purchase_transfer->purchase_lines()->createMany($purchase_lines);
            }

            //Decrease product stock from sell location and increase at purchase location
            foreach ($products as $product) {
                $qty = $this->productUtil->num_uf($product['quantity']);
                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $sell_transfer->location_id,
                        $qty,
                        0,
                        'decrease'
                    );

                    if (method_exists($this->productUtil, 'decreaseProductQuantityStore')) {
                        $this->productUtil->decreaseProductQuantityStore(
                            $product['product_id'],
                            $product['variation_id'],
                            $sell_transfer->location_id,
                            $qty,
                            $from_store,
                            "decrease",
                            0
                        );
                    }

                    $this->productUtil->updateProductQuantity(
                        $purchase_transfer->location_id,
                        $product['product_id'],
                        $product['variation_id'],
                        $qty
                    );

                    if (method_exists($this->productUtil, 'updateProductQuantityStore')) {
                        $this->productUtil->updateProductQuantityStore(
                            $purchase_transfer->location_id,
                            $product['product_id'],
                            $product['variation_id'],
                            $qty,
                            $to_store
                        );
                    }
                }
            }

            //Map sell lines with purchase lines
            $business = [
                'id' => $business_id,
                'accounting_method' => auth()->user()->business->accounting_method ?? 'fifo',
                'location_id' => $sell_transfer->location_id
            ];
            $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase');

            DB::commit();
            $output = ['success' => 1, 'msg' => __('lang_v1.stock_transfer_added_successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return redirect()->route('stock-transfers.index')->with('status', $output);
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

        $stock_adjustment_details = Transaction::join(
            'transaction_sell_lines as sl',
            'sl.transaction_id',
            '=',
            'transactions.id'
        )
            ->join('products as p', 'sl.product_id', '=', 'p.id')
            ->join('variations as v', 'sl.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->where('transactions.id', $id)
            ->where('transactions.type', 'sell_transfer')
            ->leftjoin('purchase_lines as pl', 'sl.lot_no_line_id', '=', 'pl.id')
            ->select(
                'p.name as product',
                'p.type as type',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku',
                'sl.quantity',
                'sl.unit_price',
                'pl.lot_number',
                'pl.exp_date',
                'transactions.shipping_charges',
                'transactions.additional_notes'
            )
            ->groupBy('sl.id')
            ->get();

        return view('stock_transfer.partials.details')
            ->with(compact('stock_adjustment_details'));
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
            if (request()->ajax()) {
                $business_id = auth()->user()->business_id;

                //Get sell transfer transaction
                $sell_transfer = Transaction::where('id', $id)
                    ->where('type', 'sell_transfer')
                    ->with(['sell_lines'])
                    ->first();

                //Get purchase transfer transaction
                $purchase_transfer = Transaction::where('transfer_parent_id', $sell_transfer->id)
                    ->where('type', 'purchase_transfer')
                    ->with(['purchase_lines'])
                    ->first();

                DB::beginTransaction();

                //Get purchase lines from mapping table and decrease quantity_sold
                $sell_lines = $sell_transfer->sell_lines;
                foreach ($sell_lines as $sell_line) {
                    $purchase_sell_line = TransactionSellLinesPurchaseLines::where('sell_line_id', $sell_line->id)->get();
                    foreach ($purchase_sell_line as $map) {
                        PurchaseLine::where('id', $map->purchase_line_id)
                            ->decrement('quantity_sold', $map->quantity);
                        $map->delete();
                    }
                }

                // Restore stock
                foreach ($sell_lines as $line) {
                    $this->productUtil->updateProductQuantity($sell_transfer->location_id, $line->product_id, $line->variation_id, $line->quantity);
                    if (method_exists($this->productUtil, 'updateProductQuantityStore')) {
                        $this->productUtil->updateProductQuantityStore($sell_transfer->location_id, $line->product_id, $line->variation_id, $line->quantity, $sell_transfer->store_id);
                    }

                    $this->productUtil->decreaseProductQuantity($purchase_transfer->location_id, $line->product_id, $line->variation_id, $line->quantity);
                    if (method_exists($this->productUtil, 'decreaseProductQuantityStore')) {
                        $this->productUtil->decreaseProductQuantityStore($purchase_transfer->location_id, $line->product_id, $line->variation_id, $line->quantity, $purchase_transfer->store_id);
                    }
                }

                $sell_transfer->delete();
                $purchase_transfer->delete();

                DB::commit();
                $output = ['success' => 1, 'msg' => __('lang_v1.stock_transfer_delete_success')];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }

    /**
     * Print invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = auth()->user()->business_id;
            $sell_transfer = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->where('type', 'sell_transfer')
                ->with([
                    'contact',
                    'sell_lines',
                    'sell_lines.product',
                    'sell_lines.variations',
                    'sell_lines.variations.product_variation',
                    'location',
                    'sell_lines.product.unit'
                ])
                ->first();

            $purchase_transfer = Transaction::where('business_id', $business_id)
                ->where('transfer_parent_id', $sell_transfer->id)
                ->where('type', 'purchase_transfer')
                ->first();

            $location_details = ['sell' => $sell_transfer->location, 'purchase' => $purchase_transfer->location];

            $output = ['success' => 1, 'receipt' => []];
            $output['receipt']['html_content'] = view('stock_transfer.print', compact('sell_transfer', 'location_details'))->render();
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }
        return $output;
    }

    public function getBusinessLocationStoreId($id)
    {
        $business_id = auth()->user()->business_id;
        // Assuming Store model has getStores method as in ERP
        if (method_exists(Store::class, 'getStores')) {
            $store = Store::getStores($business_id, request()->input('check_store_not'), $id, request()->input('permission'));
        } else {
            $store = Store::where('business_id', $business_id)->where('status', 1)->where('location_id', $id)->select('id', 'name')->get();
        }
        return $store;
    }
}

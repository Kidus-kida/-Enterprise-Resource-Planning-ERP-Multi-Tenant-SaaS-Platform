<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\Product;
use App\Store;
use App\StockTransferRequest;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\VariationStoreDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StockTransferRequestController extends Controller
{
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        if (request()->ajax()) {
            $business_id = auth()->user()->business_id;

            $stock_transfer_requests = StockTransferRequest::leftjoin(
                'business_locations AS rl',
                'stock_transfer_requests.request_location',
                '=',
                'rl.id'
            )
                ->leftjoin(
                    'business_locations AS rtl',
                    'stock_transfer_requests.request_to_location',
                    '=',
                    'rtl.id'
                )
                ->leftjoin(
                    'products',
                    'stock_transfer_requests.product_id',
                    '=',
                    'products.id'
                )
                ->leftjoin(
                    'stores',
                    'stock_transfer_requests.store_id',
                    '=',
                    'stores.id'
                )
                ->leftjoin(
                    'users',
                    'stock_transfer_requests.created_by',
                    '=',
                    'users.id'
                )
                ->where('stock_transfer_requests.business_id', $business_id)
                ->select(
                    'stock_transfer_requests.*',
                    'rl.name as rl',
                    'rtl.name as rtl',
                    'products.name as product_name',
                    'users.username as username',
                    'stores.name as stores_name'
                );

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $stock_transfer_requests->whereDate('stock_transfer_requests.created_at', '>=', request()->start_date);
                $stock_transfer_requests->whereDate('stock_transfer_requests.created_at', '<=', request()->end_date);
            }
            if (!empty(request()->request_location)) {
                $stock_transfer_requests->where('stock_transfer_requests.request_location', request()->request_location);
            }
            if (!empty(request()->request_to_location)) {
                $stock_transfer_requests->where('stock_transfer_requests.request_to_location', request()->request_to_location);
            }
            if (!empty(request()->category_id)) {
                $stock_transfer_requests->where('stock_transfer_requests.category_id', request()->category_id);
            }
            if (!empty(request()->sub_category_id)) {
                $stock_transfer_requests->where('stock_transfer_requests.sub_category_id', request()->sub_category_id);
            }
            if (!empty(request()->product_id)) {
                $stock_transfer_requests->where('stock_transfer_requests.product_id', request()->product_id);
            }
            if (!empty(request()->status)) {
                $stock_transfer_requests->where('stock_transfer_requests.status', request()->status);
            }

            return DataTables::of($stock_transfer_requests)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-external-link" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.edit', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.destroy', [$row->id]) . '" class="delete-request" ><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</a></li>';

                    if (auth()->user()->can('purchase.create')) {
                        $html .= '<li><a target="_blank" href="' . route('stock-transfers-request.createTransfer', [$row->id]) . '" class="create_transfer" ><i class="fa fa-exchange"></i> ' . __(" create_transfer") . '</a></li>';
                    }
                    if ($row->status == 'transit') {
                        if ($row->created_by == auth()->user()->id) {
                            $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.getReceivedTrasnfer', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-arrow-down"></i> ' . __(" received") . '</a></li>';
                        }
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('qty', '{{@format_quantity($qty)}}')
                ->editColumn('status', function ($row) {
                    return ($row->status == 'issued') ? 'approved' : $row->status;
                })
                ->addColumn('received_status', function ($row) {
                    if ($row->status == 'received') {
                        $cls = ($row->qty == $row->good_condition) ? 'success' : 'warning';
                        return '<label class="label label-' . $cls . '">' . __(" received") . '</label>';
                    }
                    return '';
                })
                ->editColumn('date', '{{@format_datetime($created_at)}}')
                ->rawColumns(['action', 'received_status'])
                ->make(true);
        }

        $business_id = auth()->user()->business_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id);
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');

        $asset_v = 1;
        return view('stock_transfer.requests.index')->with(compact('business_locations', 'categories', 'products', 'asset_v'));
    }

    public function shippment_list()
    {
        if (request()->ajax()) {
            $business_id = auth()->user()->business_id;

            $data = DB::table('transfer_shipment')
                ->leftjoin('drivers', 'transfer_shipment.driver_id', '=', 'drivers.id')
                ->leftjoin('users', 'transfer_shipment.created_by', '=', 'users.id')
                ->leftjoin('stock_transfer_requests', function ($join) {
                    $join->on(DB::raw("FIND_IN_SET(stock_transfer_requests.id, transfer_shipment.request_id)"), '>', DB::raw('0'));
                })
                ->where('transfer_shipment.business_id', $business_id)
                ->select(
                    'transfer_shipment.*',
                    'drivers.driver_name as driver_name',
                    'users.username as ship_created_by'
                )
                ->groupBy('transfer_shipment.id');

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $data->whereDate('transfer_shipment.assigned_date', '>=', request()->start_date);
                $data->whereDate('transfer_shipment.assigned_date', '<=', request()->end_date);
            }
            if (!empty(request()->status)) {
                $data->where('transfer_shipment.shipment_status', request()->status);
            }
            if (!empty(request()->driver_id)) {
                $data->where('transfer_shipment.driver_id', request()->driver_id);
            }
            if (!empty(request()->request_location)) {
                $data->where('stock_transfer_requests.request_location', request()->request_location);
            }
            if (!empty(request()->request_to_location)) {
                $data->where('stock_transfer_requests.request_to_location', request()->request_to_location);
            }
            if (!empty(request()->from_store)) {
                $data->where('stock_transfer_requests.from_store', request()->from_store);
            }
            if (!empty(request()->to_store)) {
                $data->where('stock_transfer_requests.store_id', request()->to_store);
            }
            if (!empty(request()->category_id)) {
                $data->where('stock_transfer_requests.category_id', request()->category_id);
            }
            if (!empty(request()->sub_category_id)) {
                $data->where('stock_transfer_requests.sub_category_id', request()->sub_category_id);
            }
            if (!empty(request()->product_id)) {
                $data->where('stock_transfer_requests.product_id', request()->product_id);
            }

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.showShipmentDetail', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-external-link" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.editShipping', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                    $html .= '<li><a href="#" data-href="' . route('stock-transfers-request.destroyShipping', [$row->id]) . '" class="delete-shipment" ><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</a></li>';

                    $html .= '</ul></div>';
                    return $html;
                })
                ->addColumn('select_items', function ($row) {
                    if (empty($row->request_id)) {
                        return '';
                    }
                    $request_ids = explode(',', $row->request_id);
                    $items = DB::table('stock_transfer_requests')
                        ->join('products', 'stock_transfer_requests.product_id', '=', 'products.id')
                        ->whereIn('stock_transfer_requests.id', $request_ids)
                        ->pluck('products.name')
                        ->toArray();
                    return implode(', ', $items);
                })
                ->removeColumn('id')
                ->editColumn('shipment_status', function ($row) {
                    return ($row->shipment_status == 'issued') ? 'approved' : $row->shipment_status;
                })
                ->editColumn('date', '{{@format_datetime($assigned_date)}}')
                ->rawColumns(['action', 'select_items'])
                ->make(true);
        }

        $business_id = auth()->user()->business_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        $stores = Store::where('business_id', $business_id)->pluck('name', 'id');
        $strs = DB::table('stock_transfer_requests')
            ->join('products', 'stock_transfer_requests.product_id', '=', 'products.id')
            ->where('stock_transfer_requests.business_id', $business_id)
            ->pluck('products.name', 'stock_transfer_requests.id');
        $categories = Category::forDropdown($business_id);
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');
        $driver = DB::table('drivers')->where('business_id', $business_id)->pluck('driver_name', 'id');
        $asset_v = 1;
        return view('stock_transfer.requests.shippment_list')->with(compact('business_locations', 'categories', 'products', 'stores', 'strs', 'driver', 'asset_v'));
    }

    public function showShipmentDetail($id)
    {
        $transfer_shipment = DB::table('transfer_shipment')->find($id);
        if (!$transfer_shipment) {
            return redirect()->back()->with('status', ['success' => false, 'msg' => __('messages.something_went_wrong')]);
        }

        $requestIds = explode(',', $transfer_shipment->request_id);

        $transfer_shipment = DB::table('transfer_shipment')
            ->where('transfer_shipment.id', $id)
            ->leftjoin('drivers', 'transfer_shipment.driver_id', '=', 'drivers.id')
            ->leftjoin('users', 'transfer_shipment.created_by', '=', 'users.id')
            ->leftjoin('cars', 'drivers.car_id', '=', 'cars.id')
            ->select(
                'transfer_shipment.*',
                'drivers.driver_name as driver_name',
                'users.username as created_by',
                'cars.plate_no as plate',
                'cars.brand as car_brand',
                'cars.model as car_model'
            )
            ->first();

        $requests = DB::table('stock_transfer_requests')
            ->whereIn('stock_transfer_requests.id', $requestIds)
            ->leftjoin('products', 'stock_transfer_requests.product_id', 'products.id')
            ->leftjoin('stores as ts', 'stock_transfer_requests.store_id', 'ts.id')
            ->leftjoin('stores as fs', 'stock_transfer_requests.from_store', 'fs.id')
            ->select(
                'stock_transfer_requests.*',
                'products.name as product_name',
                'ts.name as to_store',
                'fs.name as from_store'
            )
            ->get();

        return view('stock_transfer.requests.shipment_show')->with(compact('transfer_shipment', 'requests'));
    }

    public function editShipping($id)
    {
        $business_id = auth()->user()->business_id;
        $shipping = DB::table('transfer_shipment')
            ->where('transfer_shipment.id', $id)
            ->leftjoin('drivers', 'transfer_shipment.driver_id', '=', 'drivers.id')
            ->leftjoin('users', 'transfer_shipment.created_by', '=', 'users.id')
            ->select('transfer_shipment.*', 'drivers.driver_name as driver_name', 'users.username as created_by')
            ->first();

        $driver = DB::table('drivers')->where('business_id', $business_id)->pluck('driver_name', 'id');
        $items = DB::table('stock_transfer_requests')
            ->join('products', 'stock_transfer_requests.product_id', '=', 'products.id')
            ->where('stock_transfer_requests.business_id', $business_id)
            ->pluck('products.name', 'stock_transfer_requests.id');

        return view('stock_transfer.requests.shipping_edit')->with(compact('shipping', 'driver', 'items'));
    }

    public function updateShipping($id, Request $request)
    {
        try {
            $input = $request->except('_token', '_method');
            $input['created_by'] = auth()->user()->id;
            $input['request_id'] = implode(',', $request->request_id);
            if ($input['shipment_status'] == 'delivered') {
                $input['delivered_date'] = Carbon::now();
            }

            DB::table('transfer_shipment')->where('id', $id)->update($input);
            $output = ['success' => true, 'msg' => __(' shipping_updated')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with('status', $output);
    }

    public function destroyShipping($id)
    {
        try {
            DB::table('transfer_shipment')->where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __(' shipping_delete_success')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return $output;
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id);
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');
        $tostore = Store::where('business_id', $business_id)->pluck('name', 'id');

        $asset_v = 1;
        return view('stock_transfer.requests.create')->with(compact('business_locations', 'categories', 'products', 'tostore', 'asset_v'));
    }

    public function shippment()
    {
        $business_id = auth()->user()->business_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        $driver = DB::table('drivers')->where('business_id', $business_id)->pluck('driver_name', 'id');
        $requests = DB::table('stock_transfer_requests')
            ->join('products', 'stock_transfer_requests.product_id', '=', 'products.id')
            ->where('stock_transfer_requests.business_id', $business_id)
            ->where('stock_transfer_requests.status', 'issued')
            ->pluck('products.name', 'stock_transfer_requests.id');

        $asset_v = 1;
        return view('stock_transfer.requests.shippment')->with(compact('business_locations', 'driver', 'requests', 'asset_v'));
    }

    public function store(Request $request)
    {
        try {
            $input = $request->except('_token');
            $input['business_id'] = auth()->user()->business_id;
            $input['delivery_need_on'] = !empty($request->delivery_need_on) ? Carbon::parse($request->delivery_need_on)->format('Y-m-d') : date('Y-m-d');
            $input['created_by'] = auth()->user()->id;

            StockTransferRequest::create($input);
            $output = ['success' => true, 'msg' => __(' request_create_success')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with('status', $output);
    }

    public function addshipment(Request $request)
    {
        try {
            $input = $request->except('_token');
            $input['request_id'] = implode(',', $request->request_id);
            $input['created_by'] = auth()->user()->id;
            $input['business_id'] = auth()->user()->business_id;

            DB::table('transfer_shipment')->insert($input);
            $request_ids = explode(',', $input['request_id']);
            DB::table('stock_transfer_requests')->whereIn('id', $request_ids)->update(['status' => 'transit']);

            $output = ['success' => true, 'msg' => __(' request_create_success')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with('status', $output);
    }

    public function show($id)
    {
        $stock_transfer_requests = StockTransferRequest::leftjoin('business_locations AS rl', 'stock_transfer_requests.request_location', '=', 'rl.id')
            ->leftjoin('business_locations AS rtl', 'stock_transfer_requests.request_to_location', '=', 'rtl.id')
            ->leftjoin('products', 'stock_transfer_requests.product_id', '=', 'products.id')
            ->leftjoin('stores', 'stock_transfer_requests.store_id', '=', 'stores.id')
            ->leftjoin('categories as cat', 'stock_transfer_requests.category_id', '=', 'cat.id')
            ->leftjoin('categories as sub_cat', 'stock_transfer_requests.sub_category_id', '=', 'sub_cat.id')
            ->leftjoin('users', 'stock_transfer_requests.created_by', '=', 'users.id')
            ->where('stock_transfer_requests.id', $id)
            ->select(
                'stock_transfer_requests.*',
                'rl.name as rl',
                'rtl.name as rtl',
                'products.name as product_name',
                'stores.name as store_name',
                'cat.name as cat_name',
                'sub_cat.name as sub_cat_name',
                'users.username as created_by'
            )->first();

        return view('stock_transfer.requests.show')->with(compact('stock_transfer_requests'));
    }

    public function edit($id)
    {
        $business_id = auth()->user()->business_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id);
        $sub_categories = Category::where('business_id', $business_id)->where('parent_id', '!=', 0)->pluck('name', 'id');
        $products = Product::where('business_id', $business_id)->pluck('name', 'id');
        $tostore = Store::where('business_id', $business_id)->pluck('name', 'id');
        $transfer_request = StockTransferRequest::findOrFail($id);

        return view('stock_transfer.requests.edit')->with(compact('business_locations', 'categories', 'sub_categories', 'products', 'transfer_request', 'tostore'));
    }

    public function update(Request $request, $id)
    {
        try {
            $input = $request->except('_token', '_method');
            $input['business_id'] = auth()->user()->business_id;
            $input['delivery_need_on'] = !empty($request->delivery_need_on) ? Carbon::parse($request->delivery_need_on)->format('Y-m-d') : date('Y-m-d');
            $input['created_by'] = auth()->user()->id;

            StockTransferRequest::where('id', $id)->update($input);
            $output = ['success' => true, 'msg' => __(' request_update_success')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with('status', $output);
    }

    public function destroy($id)
    {
        try {
            StockTransferRequest::where('id', $id)->delete();
            $output = ['success' => true, 'msg' => __(' request_delete_success')];
        } catch (\Exception $e) {
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return $output;
    }

    public function createTransfer($id)
    {
        $business_id = auth()->user()->business_id;
        $request_transfer = StockTransferRequest::findOrFail($id);
        $to_store = Store::findOrFail($request_transfer->store_id);
        $product = Product::findOrFail($request_transfer->product_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $variation_id = Variation::where('product_id', $request_transfer->product_id)->first();

        return view('stock_transfer.requests.create_transfer2')->with(compact('business_locations', 'request_transfer', 'product', 'variation_id', 'to_store'));
    }

    public function getProductBalance(Request $request)
    {
        $currentBalance = VariationStoreDetail::where('product_id', $request->input('product_id'))
            ->where('store_id', $request->input('from_store'))
            ->value('qty_available');
        return response()->json(['current_balance' => $currentBalance]);
    }

    public function saveTransfer(Request $request)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = auth()->user()->business_id;
            DB::beginTransaction();

            $input_data = $request->only(['location_id', 'ref_no', 'transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);
            $from_store = $request->input('from_store');
            $to_store = $request->input('to_store');
            $user_id = auth()->user()->id;

            $input_data['final_total'] = $this->productUtil->num_uf($input_data['final_total']);
            $input_data['total_before_tax'] = $input_data['final_total'];
            $input_data['type'] = 'sell_transfer';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
            $input_data['status'] = 'final';
            $input_data['payment_status'] = 'paid';

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
                    $sell_lines[] = $sell_line_arr;
                    $purchase_lines[] = $purchase_line_arr;
                }
            }

            $input_data['store_id'] = $from_store;
            $sell_transfer = Transaction::create($input_data);

            $input_data['type'] = 'purchase_transfer';
            $input_data['status'] = 'approved';
            $input_data['location_id'] = $request->input('transfer_location_id');
            $input_data['transfer_parent_id'] = $sell_transfer->id;
            $input_data['store_id'] = $to_store;
            $purchase_transfer = Transaction::create($input_data);

            if (!empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, $sell_transfer->location_id);
            }
            if (!empty($purchase_lines)) {
                $purchase_transfer->purchase_lines()->createMany($purchase_lines);
            }

            foreach ($products as $product) {
                if ($product['enable_stock']) {
                    $qty = $this->productUtil->num_uf($product['quantity']);
                    $this->productUtil->decreaseProductQuantity($product['product_id'], $product['variation_id'], $sell_transfer->location_id, $qty);
                    if (method_exists($this->productUtil, 'decreaseProductQuantityStore')) {
                        $this->productUtil->decreaseProductQuantityStore($product['product_id'], $product['variation_id'], $sell_transfer->location_id, $qty, $from_store);
                    }
                }
            }

            $business = ['id' => $business_id, 'accounting_method' => auth()->user()->business->accounting_method ?? 'fifo', 'location_id' => $sell_transfer->location_id];
            $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase');

            StockTransferRequest::where('id', $request->request_id)->update(['status' => 'issued', 'transaction_id' => $purchase_transfer->id]);

            DB::commit();
            $output = ['success' => 1, 'msg' => __(' stock_transfer_added_successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
        return redirect()->route('stock-transfers-request.index')->with('status', $output);
    }

    public function stopNotification($id)
    {
        StockTransferRequest::where('id', $id)->update(['notification' => 'stop']);
        return redirect()->back();
    }

    public function getReceivedTrasnfer($id)
    {
        $rquest_transfer = StockTransferRequest::where('id', $id)->with(['products'])->first();
        return view('stock_transfer.requests.received_transfer')->with(compact('rquest_transfer'));
    }

    public function postReceivedTransfer($id, Request $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->except('_token');
            $input['status'] = 'received';
            $transfer_request = StockTransferRequest::findOrFail($id);
            $transfer_request->update($input);

            $variation = Variation::where('product_id', $transfer_request->product_id)->first();
            $transaction = Transaction::findOrFail($transfer_request->transaction_id);
            $transaction->status = 'received';
            $transaction->save();

            $this->productUtil->updateProductQuantity($transfer_request->request_location, $transfer_request->product_id, $variation->id, $input['good_condition']);
            if (method_exists($this->productUtil, 'updateProductQuantityStore')) {
                $this->productUtil->updateProductQuantityStore($transfer_request->request_location, $transfer_request->product_id, $variation->id, $input['good_condition'], $transfer_request->store_id);
            }

            DB::commit();
            $output = ['success' => 1, 'msg' => __(' qty_update_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
        return redirect()->back()->with('status', $output);
    }
}

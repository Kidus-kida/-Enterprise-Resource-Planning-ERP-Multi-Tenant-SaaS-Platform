<?php

namespace Modules\StockAdjustment\Http\Controllers;
use App\Http\Controllers\Controller;
use App\BusinessLocation;

use App\Account;
use App\AccountGroup;
use App\AccountType;
use App\Business;
use App\Store;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Category;
use Modules\StockAdjustment\Models\StockAdjustmentSetting;


use Datatables;
use DB;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\DataTables\StockAdjustmentDataTable;

class StockAdjustmentSettings extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
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
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('stockadjustment-settings.create');
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
        $categories = Category::forDropdown($business_id, 'product');
        $sub_categories = Category::where('business_id', $business_id)
                            ->whereNotNull('parent_id')
                            ->pluck('name', 'id')
                            ->toArray();
        
        $fg_group = AccountGroup::getGroupByName("Finished Goods Account");
        $rm_group = AccountGroup::getGroupByName("Raw Material Account");
        $os_group = AccountGroup::getGroupByName("Other Stocks");

        $stock_account_groups = [
            (!empty($fg_group) ? $fg_group->id : 0) => "Finished Goods Account",
            (!empty($rm_group) ? $rm_group->id : 0) => "Raw Material Account",
            (!empty($os_group) ? $os_group->id : 0) => "Other Stocks",
        ];

        $settings = StockAdjustmentSetting::where('business_id', $business_id)->first();
        \Log::info('StockAdjustmentSettings create: retrieved settings', ['settings' => $settings, 'business_id' => $business_id]);
        
        $accounts = Account::where('business_id', $business_id)->pluck('name', 'id');

        // Simple Date Logic: Just use ISO or standard format
        $formatted_date = !empty($settings->date) ? \Carbon\Carbon::parse($settings->date)->format('Y-m-d H:i') : \Carbon\Carbon::now()->format('Y-m-d H:i');

        return view('stockadjustment::create')
                 ->with(compact('business_locations', 'categories', 'sub_categories', 'stock_account_groups', 'accounts', 'settings', 'formatted_date'));
    }

    
    public function get_account_by($by, $id){
        $accounts = [];
        $business_id = request()->session()->get('user.business_id');
        if($by == "group"){
            $accounts =  Account::where('business_id', $business_id)->where('asset_type', $id)->pluck('name', 'id')->toArray();
        }elseif($by == "type"){
             $type_id = AccountType::getAccountTypeIdOfType($id, $business_id);
            $accounts =  Account::where('business_id', $business_id)->where('account_type_id', $type_id)->pluck('name', 'id')->toArray();
        }
        
        $options = "<option value=''>Please Select</option>";
        
        foreach($accounts as $key => $account){
            $options .= "<option value='".$key."'>".$account."</option>";
        }
        
        return $options;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->only(['date', 'adjustment_type', 'category_id', 'sub_category_id', 'account_to_link_id', 'stock_account_id', 'stock_account_group_id']);
            
            $business_id = auth()->user()->business_id;
            
            // Simple Parsing: Use Carbon::parse which is flexible
            try {
                $parsed_date = \Carbon\Carbon::parse($input['date']);
            } catch (\Exception $e) {
                $parsed_date = \Carbon\Carbon::now();
            }

            $input_data = [
                'business_id' => $business_id,
                'date' => $parsed_date->toDateTimeString(),
                'adjustment_type' => $input['adjustment_type'],
                'category_id' => $input['category_id'],
                'sub_category_id' => $input['sub_category_id'],
                'account_to_link' => $input['account_to_link_id'],
                'stock_group' => $input['stock_account_group_id'],
                'stock_account' => $input['stock_account_id']
            ];

            $match_data = [
                'business_id' => $business_id,
                'adjustment_type' => $input['adjustment_type'],
                'category_id' => $input['category_id'],
                'sub_category_id' => $input['sub_category_id']
            ];

            \Log::info('Attempting to save StockAdjustmentSetting', ['data' => $input_data]);
            
            $result = StockAdjustmentSetting::updateOrCreate($match_data, $input_data);
            
            \Log::info('StockAdjustmentSetting save result', ['result' => $result]);
            
            DB::commit();
            
            $notification = notify(__('Stock adjustment setting added successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
             $notification = notify(__('Something went wrong'), 'error');
        }
        
        return redirect()->back()->with($notification);
    }
 
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $stockAdjustment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $stockAdjustment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
    }

    
}

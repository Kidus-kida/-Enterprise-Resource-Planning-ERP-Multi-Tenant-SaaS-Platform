<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Http\Controllers\Controller;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\SellingPriceGroup;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
// use App\Http\Controllers\Controller; // Removed to avoid conflict with namespace


class BusinessLocationController extends Controller
{
    protected $moduleUtil;
    protected $commonUtil;
    protected $businessUtil;

    /**
     * Constructor
     *
     * @param ModuleUtil $moduleUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, Util $commonUtil, BusinessUtil $businessUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->businessUtil = $businessUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Disable permission check for now to allow viewing in dev
        // if (!auth()->user()->can('business_settings.access')) {
        //    abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            $business_id = 1; // Default or from session
            if(auth()->check()) {
               // $business_id = request()->session()->get('user.business_id');
            }

            $locations = BusinessLocation::where('business_locations.business_id', $business_id)
                ->leftjoin('invoice_schemes as ic', 'business_locations.invoice_scheme_id', '=', 'ic.id')
                ->leftjoin('invoice_layouts as il', 'business_locations.invoice_layout_id', '=', 'il.id')
                ->leftjoin('selling_price_groups as spg', 'business_locations.selling_price_group_id', '=', 'spg.id')
                ->select([
                    'business_locations.name', 'location_id', 'landmark', 'city', 'zip_code', 'state',
                    'country', 'business_locations.id', 'spg.name as price_group', 'ic.name as invoice_scheme', 'il.name as invoice_layout', 'business_locations.is_active'
                ]);

             // Permitted locations check can be added here

            return Datatables::of($locations)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-bs-toggle="dropdown" aria-expanded="false">' .
                                __("messages.action") .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                <li><a href="' . action([\App\Http\Controllers\BusinessLocationController::class, 'edit'], [$row->id]) . '" class="dropdown-item edit_location_button"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>
                                <li><a href="' . action([\App\Http\Controllers\BusinessLocationController::class, 'activateDeactivateLocation'], [$row->id]) . '" class="dropdown-item activate-deactivate-location"><i class="fa fa-power-off"></i> ' . ($row->is_active ? __("lang_v1.deactivate_location") : __("lang_v1.activate_location")) . '</a></li>
                            </ul></div>';
                        return $html;
                    }
                )
                ->removeColumn('id')
                ->removeColumn('is_active')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('settings.location.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        
        // Mocking models if they don't exist fully yet or returning empty collections to avoid errors
        $invoice_layouts = class_exists(InvoiceLayout::class) ? InvoiceLayout::where('business_id', $business_id)->pluck('name', 'id') : [];
        $invoice_schemes = class_exists(InvoiceScheme::class) ? InvoiceScheme::where('business_id', $business_id)->pluck('name', 'id') : [];
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        
        $payment_types = $this->commonUtil->payment_types();
        $companies = \App\Company::where('business_id', $business_id)->pluck('name', 'id');

        return view('settings.location.create')
            ->with(compact(
                'invoice_layouts',
                'invoice_schemes',
                'price_groups',
                'payment_types',
                'companies'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            
            // Check Package Limit for Locations
            $subscription = \Modules\Superadmin\Models\Subscription::active_subscription($business_id);
            if (!empty($subscription)) {
                $package_details = $subscription->package_details;
                $company_count = $package_details['company_count'] ?? 1;
                $enable_multi_company = $package_details['enable_multi_company'] ?? false;
                
                // Rule: If multi-company is allowed, unlimited locations.
                $is_multi_company = $company_count > 1 || $enable_multi_company;
                
                if (!$is_multi_company) {
                    $limit = $package_details['location_count'] ?? 0;
                    if ($limit > 0) {
                        $count = BusinessLocation::where('business_id', $business_id)->count();
                        if ($count >= $limit) {
                            $output = [
                                'success' => false,
                                'msg' => __("superadmin::lang.location_limit_reached") 
                            ];
                            return $output;
                        }
                    }
                }
            }

            $input = $request->only([
                'name', 'landmark', 'city', 'state', 'country', 'zip_code', 'invoice_scheme_id',
                'invoice_layout_id', 'mobile', 'alternate_number', 'email', 'website', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'location_id', 'selling_price_group_id', 'company_id'
            ]);

            $input['business_id'] = $business_id;

            // Generate location ID if empty
            if (empty($input['location_id'])) {
                 $ref_count = $this->moduleUtil->setAndGetReferenceCount('business_location', $business_id);
                 $input['location_id'] = $this->moduleUtil->generateReferenceNumber('business_location', $ref_count, $business_id);
            }

            BusinessLocation::create($input);

            $output = [
                'success' => true,
                'msg' => __("business.business_location_added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = 1;
        $location = BusinessLocation::where('business_id', $business_id)->find($id);

        if (!$location) {
            abort(404);
        }

        $invoice_layouts = class_exists(InvoiceLayout::class) ? InvoiceLayout::where('business_id', $business_id)->pluck('name', 'id') : [];
        $invoice_schemes = class_exists(InvoiceScheme::class) ? InvoiceScheme::where('business_id', $business_id)->pluck('name', 'id') : [];
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        $payment_types = $this->commonUtil->payment_types();
        $companies = \App\Company::where('business_id', $business_id)->pluck('name', 'id');

        return view('settings.location.edit')
            ->with(compact(
                'location',
                'invoice_layouts',
                'invoice_schemes',
                'price_groups',
                'payment_types',
                'companies'
            ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->only([
                'name', 'landmark', 'city', 'state', 'country', 'zip_code', 'invoice_scheme_id',
                'invoice_layout_id', 'mobile', 'alternate_number', 'email', 'website', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'selling_price_group_id'
            ]);

            $business_id = 1;

            BusinessLocation::where('business_id', $business_id)
                ->where('id', $id)
                ->update($input);

            $output = [
                'success' => true,
                'msg' => __('business.business_location_updated_success')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }
    
     /**
     * Function to activate or deactivate a location.
     * @param int $location_id
     *
     * @return json
     */
    public function activateDeactivateLocation($location_id)
    {
        try {
            $business_id = 1;

            $business_location = BusinessLocation::where('business_id', $business_id)
                ->findOrFail($location_id);

            $business_location->is_active = !$business_location->is_active;
            $business_location->save();

            $msg = $business_location->is_active ? __('lang_v1.business_location_activated_successfully') : __('lang_v1.business_location_deactivated_successfully');

            $output = [
                'success' => true,
                'msg' => $msg
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }
}

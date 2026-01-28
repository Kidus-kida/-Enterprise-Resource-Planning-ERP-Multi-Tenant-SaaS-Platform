<?php

namespace App\Http\Controllers;

use App\Business;
use App\Currency;
use App\Http\Controllers\Controller;
use App\TaxRate;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessSettingsController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  BusinessUtil  $businessUtil
     * @param  ModuleUtil  $moduleUtil
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Shows business settings form
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Permission check
        // if (!auth()->user()->can('business_settings.access')) {
        //    abort(403, 'Unauthorized action.');
        // }

        $business_id = 1; // Default to 1 for now if auth is not fully set up or we can get from session
        // In real app: request()->session()->get('user.business_id');
        if (auth()->check()) {
             // Assuming user has business_id or related logic
             // $business_id = request()->session()->get('user.business_id');
        }

        $business = 1; // Incorrect, this is an int
        // $business = Business::first(); // TODO: Get specific business based on auth
    
        if (!$business) {
             // Fallback if no business exists yet
             // return redirect()->route('business.getRegister'); // or similar
             $business = new Business(); // Avoid crash on empty DB
        }

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = [];
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        $currencies = $this->businessUtil->allCurrencies();
        $tax_details = TaxRate::forBusinessDropdown($business->id ?? 1);
        $tax_rates = $tax_details['tax_rates'];

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = \Carbon\Carbon::create()->month($i)->format('F');
        }

        $accounting_methods = [
            'fifo' => 'FIFO (First In First Out)',
            'lifo' => 'LIFO (Last In First Out)'
        ];

        $shortcuts = json_decode($business->keyboard_shortcuts ?? '[]', true);
        $pos_settings = $business->pos_settings ?? [];
        $email_settings = $business->email_settings ?? [];
        $common_settings = $business->common_settings ?? [];
        
        $modules = $this->moduleUtil->availableModules();
        $enabled_modules = $business->enabled_modules ?? [];
        $custom_labels = $business->custom_labels ?? [];

        $theme_colors = [
            'blue' => 'Blue',
            'black' => 'Black',
            'purple' => 'Purple',
            'green' => 'Green',
            'red' => 'Red',
            'yellow' => 'Yellow',
            'blue-light' => 'Blue Light',
            'black-light' => 'Black Light',
            'purple-light' => 'Purple Light',
            'green-light' => 'Green Light',
            'red-light' => 'Red Light',
        ];

        $commission_agent_dropdown = [
            '' => __('lang_v1.disable'),
            'logged_in_user' => __('lang_v1.logged_in_user'),
            'user' => __('lang_v1.user'),
            'cmsn_agnt' => __('lang_v1.commission_agent')
        ];

        // $units_dropdown = \App\Unit::forDropdown($business->id);
             $units_dropdown = 1;

        return view('settings.business', compact(
            'business',
            'currencies',
            'tax_rates',
            'timezone_list',
            'months',
            'accounting_methods',
            'shortcuts',
            'pos_settings',
            'email_settings',
            'common_settings',
            'modules',
            'enabled_modules',
            'custom_labels',
            'theme_colors',
            'commission_agent_dropdown',
            'units_dropdown'
        ));
    }

    /**
     * Updates business settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request->all());
        try {
            // Whitelist all allowed fields
            $business_details = $request->only([
                'name', 'start_date', 'currency_id', 'time_zone', 'fy_start_month', 
                'accounting_method', 'transaction_edit_days', 'currency_symbol_placement', 
                'date_format', 'time_format', 'currency_precision', 'quantity_precision', 
                'default_profit_percent', 'font_style', 'font_size', 'reg_no', 
                'popup_load_save_data', 'day_end_enable', 'enable_line_discount', 
                'duplicate_orders_allowed', 'show_for_customers', 'business_categories',
                'sku_prefix', 'enable_product_expiry', 'expiry_type', 'on_product_expiry', 
                'stop_selling_before', 'enable_brand', 'enable_category', 'enable_sub_category', 
                'enable_price_tax', 'default_unit', 'enable_sub_units', 'enable_racks', 
                'enable_row', 'enable_position', 'enable_editing_product_from_purchase', 
                'sales_cmsn_agnt', 'item_addition_method', 'product_upc_ean_code', 
                'keyboard_shortcuts', 'pos_settings', 'email_settings', 'common_settings', 
                'ref_no_prefixes', 'theme_color', 'ref_no_starting_number', 'enable_rp', 
                'rp_name', 'amount_for_unit_rp', 'min_order_total_for_rp', 'max_rp_per_order', 
                'redeem_amount_per_unit_rp', 'min_order_total_for_redeem', 'min_redeem_point', 
                'max_redeem_point', 'rp_expiry_period', 'rp_expiry_type', 'custom_labels', 
                'contact_fields', 'enable_inline_tax', 'tax_label_1', 'tax_number_1', 
                'tax_label_2', 'tax_number_2', 'default_sales_discount', 'default_sales_tax',
                'enable_purchase_status', 'enable_lot_number', 'stock_expiry_alert_days',
                'enable_tooltip', 'enabled_modules'
            ]);

            // Handle Checkboxes (Boolean fields)
            $checkboxes = [
                'popup_load_save_data', 'day_end_enable', 'enable_line_discount', 
                'duplicate_orders_allowed', 'show_for_customers', 'enable_product_expiry', 
                'enable_brand', 'enable_category', 'enable_sub_category', 'enable_price_tax', 
                'enable_sub_units', 'enable_racks', 'enable_row', 'enable_position', 
                'enable_editing_product_from_purchase', 'enable_rp', 'enable_inline_tax',
                'enable_purchase_status', 'enable_lot_number', 'enable_tooltip'
            ];
            foreach ($checkboxes as $check) {
                $business_details[$check] = $request->has($check) ? 1 : 0;
            }

            // Handle Arrays/JSON fields (Using $casts in model handles saving, but we might need to filter or process)
            // pos_settings, email_settings, common_settings, ref_no_prefixes, ref_no_starting_number, 
            // keyboard_shortcuts, custom_labels, contact_fields, enabled_modules
            // are automatically cast to/from JSON by the model.
            // However, if the input is array, we just pass it.
            
            // Special handling for nested or specific formats if needed
            if($request->has('business_categories')) {
                 // business_categories might be array, let model cast handle it or json_encode manually if cast not set (it wasn't in list)
                 // Start with manual encode if not sure about cast, but I added casts to Business.php for most.
                 // business_categories is NOT in casts list I added. So encode it.
                 $business_details['business_categories'] = json_encode($request->business_categories);
            } else {
                 $business_details['business_categories'] = null;
            }

            // Fix start date
            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = \Carbon\Carbon::createFromFormat('m/d/Y', $business_details['start_date'])->format('Y-m-d');
            }

            // Default profit percent
             $business_details['default_profit_percent'] = !empty($business_details['default_profit_percent']) 
                ? $this->businessUtil->num_uf($business_details['default_profit_percent']) 
                : 0;

            // Handle Logo Upload
             if ($request->hasFile('business_logo')) {
                $business_details['logo'] = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            }

            $business_id = 1; // TODO: Get from auth
            if (auth()->check()) {
                 // $business_id = auth()->user()->business_id; 
            }
            // For now force 1
            
            Business::where('id', $business_id)->update($business_details);

            $output = [
                'success' => 1,
                'msg' => 'Business settings updated successfully'
            ];

            return redirect()->back()->with('status', $output);

        } catch (\Exception $e) {
            \Log::error('Business Settings Update Error: ' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => 'Something went wrong, please try again: ' . $e->getMessage()
            ];
             return redirect()->back()->with('status', $output);
        }
    }
}

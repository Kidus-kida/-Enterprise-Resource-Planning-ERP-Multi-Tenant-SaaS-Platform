<?php

namespace App\Http\Controllers;

use App\Business;
use App\Http\Controllers\Controller;
use App\TaxRate;
use App\Unit;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use DateTimeZone;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BusinessSettingsController extends Controller
{
    protected $businessUtil;
    protected $moduleUtil;

    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        // Fetch the business record from the current tenant database
        $business = Business::first();

        if (!$business) {
            $business = Business::create(['name' => 'New Business', 'currency_id' => 1]);
        }

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = [];
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        $currencies = $this->businessUtil->allCurrencies();
        $tax_details = TaxRate::forBusinessDropdown($business->id);
        $tax_rates = $tax_details['tax_rates'];

        // Fix: Fetch units using correctly existing method for the Product tab dropdown
        $units_dropdown = Unit::forDropdown($business->id);

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create()->month($i)->format('F');
        }

        $accounting_methods = [
            'fifo' => 'FIFO (First In First Out)',
            'lifo' => 'LIFO (Last In First Out)'
        ];

        $modules = $this->moduleUtil->availableModules();

        $theme_colors = [
            'blue' => 'Blue', 'black' => 'Black', 'purple' => 'Purple',
            'green' => 'Green', 'red' => 'Red', 'yellow' => 'Yellow'
        ];

        $commission_agent_dropdown = [
            '' => __('lang_v1.disable'),
            'logged_in_user' => __('lang_v1.logged_in_user'),
            'user' => __('lang_v1.user'),
            'cmsn_agnt' => __('lang_v1.commission_agent')
        ];

        // Missing variables for partials
        $common_settings = !empty($business->common_settings) ? $business->common_settings : [];
        $search_product_settings = !empty($business->pos_settings['search_product_settings']) ? $business->pos_settings['search_product_settings'] : [];

        return view('settings.business', compact(
            'business', 'currencies', 'tax_rates', 'timezone_list', 'months',
            'accounting_methods', 'modules', 'theme_colors',
            'commission_agent_dropdown', 'units_dropdown',
            'common_settings', 'search_product_settings'
        ));
    }

    public function update(Request $request)
    {
        try {
            $business = Business::first();

            // Whitelist fields from the form
            $business_details = $request->only([
                'name', 'start_date', 'currency_id', 'time_zone', 'fy_start_month',
                'accounting_method', 'transaction_edit_days', 'currency_symbol_placement',
                'date_format', 'time_format', 'currency_precision', 'quantity_precision',
                'default_profit_percent', 'font_style', 'font_size', 'reg_no',
                'sku_prefix', 'expiry_type', 'on_product_expiry', 'stop_selling_before',
                'sales_cmsn_agnt', 'item_addition_method', 'theme_color'
            ]);

            // Handle Checkboxes (convert to 1 or 0)
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

            // Handle JSON/Array fields
            $business_details['pos_settings'] = $request->input('pos_settings');
            $business_details['email_settings'] = $request->input('email_settings');
            $business_details['common_settings'] = $request->input('common_settings');
            $business_details['enabled_modules'] = $request->input('enabled_modules');
            $business_details['custom_labels'] = $request->input('custom_labels');

            // Format Start Date
            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = Carbon::parse($business_details['start_date'])->format('Y-m-d');
            }

            // Unformat numeric percent
            if (isset($business_details['default_profit_percent'])) {
                $business_details['default_profit_percent'] = $this->businessUtil->num_uf($business_details['default_profit_percent']);
            }

            // Handle Logo Upload
            if ($request->hasFile('business_logo')) {
                $business_details['logo'] = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            }

            // Save the data
            $business->update($business_details);

            return redirect()->back()->with('status', [
                'success' => 1,
                'msg' => 'Business settings updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Business Settings Update Error: ' . $e->getMessage());
            return redirect()->back()->with('status', [
                'success' => 0,
                'msg' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

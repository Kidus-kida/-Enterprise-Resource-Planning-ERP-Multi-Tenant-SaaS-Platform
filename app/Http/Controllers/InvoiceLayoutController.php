<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\InvoiceLayout;
use App\Utils\Util;
use Illuminate\Http\Request;
use Validator;

class InvoiceLayoutController extends Controller
{
    protected $commonUtil;
    protected $designs;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->designs = [
            'classic' => 'Classic',
            'elegant' => 'Elegant',
            'detailed' => 'Detailed',
            'columnize-taxes' => 'Columnize Taxes',
            'slim' => 'Slim',
            'a5-potrait' => 'A5 Portrait',
            'a5-landscape' => 'A5 Landscape', 
            'a4-potrait' => 'A4 Portrait',
            'a4-landscape' => 'A4 Landscape', 
        ];
    }

    public function create()
    {
        $designs = $this->designs;
        return view('settings.invoice.layout.create', compact('designs'));
    }

    public function store(Request $request)
    {
        try {
            $input = $request->only(['name', 'header_align', 'header_text', 'font_size', 'header_font_size','footer_font_size', 'business_name_font_size', 'invoice_heading_font_size',
                'invoice_no_prefix', 'invoice_heading', 'sub_total_label', 'discount_label', 'tax_label', 'total_label', 'highlight_color', 'footer_text', 'invoice_heading_not_paid', 'invoice_heading_paid', 'total_due_label', 'customer_label', 'paid_label', 'sub_heading_line1', 'sub_heading_line2',
                'sub_heading_line3', 'sub_heading_line4', 'sub_heading_line5',
                'table_product_label', 'table_qty_label', 'table_unit_price_label',
                'table_subtotal_label', 'client_id_label', 'date_label', 'quotation_heading', 'quotation_no_prefix', 'design', 'client_tax_label', 'cat_code_label', 'cn_heading', 'cn_no_label', 'cn_amount_label', 'sales_person_label', 'prev_bal_label', 'date_time_format', 'common_settings', 'change_return_label', 'logo_height', 'logo_width', 'logo_margin_top', 'logo_margin_bottom']);

            $business_id = 1;
            $input['business_id'] = $business_id;

            // Checkboxes
            $checkboxes = ['show_business_name', 'show_location_name', 'show_landmark', 'show_city', 'show_state', 'show_country', 'show_zip_code', 'show_mobile_number', 'show_alternate_number', 'show_email', 'show_tax_1', 'show_tax_2', 'show_logo', 'show_barcode', 'show_payments', 'show_customer', 'show_client_id',
                'show_brand', 'show_sku', 'show_cat_code', 'show_sale_description', 'show_sales_person', 'show_expiry', 'show_lot', 'show_previous_bal', 'show_image', 'show_reward_point'];
            foreach ($checkboxes as $name) {
                $input[$name] = !empty($request->input($name)) ? 1 : 0;
            }

            // Upload Logo (assuming commonUtil has uploadFile method matching Old ERP)
            // If not we need to check Util.php
            // $logo_name = $this->commonUtil->uploadFile($request, 'logo', 'invoice_logos');
            // Mock implementation for now
            if ($request->hasFile('logo')) {
                 // $input['logo'] = ... 
            }

            InvoiceLayout::create($input);

            $output = ['success' => 1, 'msg' => 'Invoice Layout added successfully'];

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => "Something went wrong"];
        }

        return redirect()->route('settings.invoice.index')->with('status', $output);
    }

    public function edit($id)
    {
        $invoice_layout = InvoiceLayout::findOrFail($id);
        $designs = $this->designs;
        return view('settings.invoice.layout.edit', compact('invoice_layout', 'designs'));
    }

    public function update(Request $request, $id)
    {
        try {
            $input = $request->only(['name', 'header_align', 'header_text', 'font_size', 'header_font_size','footer_font_size', 'business_name_font_size', 'invoice_heading_font_size',
                'invoice_no_prefix', 'invoice_heading', 'sub_total_label', 'discount_label', 'tax_label', 'total_label', 'highlight_color', 'footer_text', 'invoice_heading_not_paid', 'invoice_heading_paid', 'total_due_label', 'customer_label', 'paid_label', 'sub_heading_line1', 'sub_heading_line2',
                'sub_heading_line3', 'sub_heading_line4', 'sub_heading_line5',
                'table_product_label', 'table_qty_label', 'table_unit_price_label',
                'table_subtotal_label', 'client_id_label', 'date_label', 'quotation_heading', 'quotation_no_prefix', 'design', 'client_tax_label', 'cat_code_label', 'cn_heading', 'cn_no_label', 'cn_amount_label', 'sales_person_label', 'prev_bal_label', 'date_time_format', 'common_settings', 'change_return_label', 'logo_height', 'logo_width', 'logo_margin_top', 'logo_margin_bottom']);
            
            $business_id = 1;

            $checkboxes = ['show_business_name', 'show_location_name', 'show_landmark', 'show_city', 'show_state', 'show_country', 'show_zip_code', 'show_mobile_number', 'show_alternate_number', 'show_email', 'show_tax_1', 'show_tax_2', 'show_logo', 'show_barcode', 'show_payments', 'show_customer', 'show_client_id',
                'show_brand', 'show_sku', 'show_cat_code', 'show_sale_description', 'show_sales_person', 'show_expiry', 'show_lot', 'show_previous_bal', 'show_image', 'show_reward_point'];
            foreach ($checkboxes as $name) {
                $input[$name] = !empty($request->input($name)) ? 1 : 0;
            }

            InvoiceLayout::where('id', $id)->where('business_id', $business_id)->update($input);
            $output = ['success' => 1, 'msg' => 'Invoice Layout updated successfully'];

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => "Something went wrong"];
        }

        return redirect()->route('settings.invoice.index')->with('status', $output);
    }
}

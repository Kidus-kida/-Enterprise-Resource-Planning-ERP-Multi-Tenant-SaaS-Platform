<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Invoice Schemes
        if (DB::table('invoice_schemes')->count() == 0) {
            DB::table('invoice_schemes')->insert([
                [
                    'business_id' => 1,
                    'name' => 'Default',
                    'scheme_type' => 'blank',
                    'prefix' => '',
                    'start_number' => 1,
                    'invoice_count' => 0,
                    'total_digits' => 4,
                    'is_default' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'business_id' => 1,
                    'name' => 'Format 1',
                    'scheme_type' => 'year',
                    'prefix' => 'INV-',
                    'start_number' => 1,
                    'invoice_count' => 0,
                    'total_digits' => 4,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        // Seed Invoice Layouts
        if (DB::table('invoice_layouts')->count() == 0) {
            DB::table('invoice_layouts')->insert([
                [
                     'business_id' => 1,
                    'name' => 'Default',
                    'header_text' => null,
                    'invoice_no_prefix' => 'Invoice No.',
                    'invoice_heading' => 'Invoice',
                    'sub_total_label' => 'Subtotal',
                    'discount_label' => 'Discount',
                    'tax_label' => 'Tax',
                    'total_label' => 'Total',
                    'show_logo' => 1,
                    'show_business_name' => 1,
                    'show_location_name' => 1,
                    'show_landmark' => 1,
                    'show_city' => 1,
                    'show_state' => 1,
                    'show_zip_code' => 1,
                    'show_country' => 1,
                    'show_mobile_number' => 1,
                    'show_email' => 1,
                    'show_tax_1' => 1,
                    'show_tax_2' => 0,
                    'show_barcode' => 0,
                    'show_payments' => 1,
                    'show_customer' => 1,
                    'customer_label' => 'Customer',
                    'highlight_color' => '#000000',
                    'footer_text' => '',
                    'module_info' => null,
                    'common_settings' => null,
                    'is_default' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }
    }
}

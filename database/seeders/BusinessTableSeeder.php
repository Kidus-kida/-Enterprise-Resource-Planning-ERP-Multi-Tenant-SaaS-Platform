<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'MD Code Inc.',
                'currency_id' => 1,
                'start_date' => '2024-01-01',
                'default_profit_percent' => 25,
                'owner_id' => 1,
                'time_zone' => 'Africa/Addis_Ababa',
                'fy_start_month' => 1,
                'accounting_method' => 'fifo',
                'default_sales_discount' => 0,
                'sell_price_tax' => 'includes',
                'logo' => null,
                'sku_prefix' => null,
                'enable_product_expiry' => 0,
                'expiry_type' => 'add_expiry',
                'on_product_expiry' => 'keep_selling',
                'stop_selling_before' => 0,
                'enable_tooltip' => 1,
                'purchase_in_diff_currency' => 0,
                'purchase_currency_id' => null,
                'p_exchange_rate' => 1,
                'transaction_edit_days' => 30,
                'stock_expiry_alert_days' => 30,
                'keyboard_shortcuts' => null,
                'pos_settings' => null,
                'weighing_scale_setting' => null,
                'enable_brand' => 1,
                'enable_category' => 1,
                'enable_sub_category' => 1,
                'enable_price_tax' => 1,
                'enable_purchase_status' => 1,
                'enable_lot_number' => 0,
                'default_unit' => null,
                'enable_sub_units' => 0,
                'enable_racks' => 0,
                'enable_row' => 0,
                'enable_position' => 0,
                'enable_editing_product_from_purchase' => 1,
                'sales_cmsn_agnt' => null,
                'item_addition_method' => '1',
                'enable_inline_tax' => 1,
                'currency_symbol_placement' => 'before',
                'enabled_modules' => null,
                'date_format' => 'm/d/Y',
                'time_format' => '12',
                'ref_no_prefixes' => null,
                'theme_color' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

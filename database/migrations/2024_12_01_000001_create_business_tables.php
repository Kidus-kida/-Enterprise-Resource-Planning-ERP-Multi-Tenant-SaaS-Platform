<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            // Tenancy fields
            $table->string('tenant_id')->unique()->nullable();
            $table->string('subdomain')->unique()->nullable();
            $table->boolean('is_active')->default(1);
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // Basic business information
            $table->string('name');
            $table->string('currency_id')->nullable();
            $table->date('start_date')->nullable();
            $table->string('tax_number_1')->nullable();
            $table->string('tax_label_1')->nullable();
            $table->string('tax_number_2')->nullable();
            $table->string('tax_label_2')->nullable();
            $table->enum('default_sales_tax', ['includes', 'excludes'])->nullable();
            $table->string('default_profit_percent')->default(0);
            $table->unsignedInteger('owner_id');
            $table->string('time_zone')->default('Africa/Addis_Ababa');
            $table->string('fy_start_month')->default('1');
            $table->enum('accounting_method', ['fifo', 'lifo', 'avco'])->default('fifo');
            $table->decimal('default_sales_discount', 5, 2)->nullable();
            $table->enum('sell_price_tax', ['includes', 'excludes'])->default('includes');
            $table->string('logo')->nullable();
            $table->string('sku_prefix')->nullable();

            // Product settings
            $table->boolean('enable_product_expiry')->default(0);
            $table->enum('expiry_type', ['add_expiry', 'add_manufacturing'])->default('add_expiry');
            $table->string('on_product_expiry')->default('keep_selling');
            $table->integer('stop_selling_before')->nullable();
            $table->boolean('enable_tooltip')->default(1);
            $table->decimal('purchase_in_diff_currency', 22, 4)->default(0)->nullable();
            $table->string('purchase_currency_id')->nullable();
            $table->decimal('p_exchange_rate', 20, 3)->default(1);
            $table->integer('transaction_edit_days')->unsigned()->default(30);
            $table->integer('stock_expiry_alert_days')->nullable();
            $table->string('keyboard_shortcuts')->nullable();
            $table->text('pos_settings')->nullable();
            $table->text('weighing_scale_setting')->nullable();
            $table->boolean('enable_brand')->default(1);
            $table->boolean('enable_category')->default(1);
            $table->boolean('enable_sub_category')->default(1);
            $table->boolean('enable_price_tax')->default(1);
            $table->boolean('enable_purchase_status')->default(1);
            $table->boolean('enable_lot_number')->default(0);
            $table->boolean('default_unit')->nullable();
            $table->boolean('enable_sub_units')->default(0);
            $table->boolean('enable_racks')->default(0);
            $table->boolean('enable_row')->default(0);
            $table->boolean('enable_position')->default(0);
            $table->boolean('enable_editing_product_from_purchase')->default(1);
            $table->text('sales_cmsn_agnt')->nullable();
            $table->enum('item_addition_method', ['1', '2'])->default(1);
            $table->boolean('enable_inline_tax')->default(1);
            $table->string('currency_symbol_placement')->default('before');
            $table->text('enabled_modules')->nullable();
            $table->string('date_format')->default('m/d/Y');
            $table->string('time_format')->default('12');
            $table->text('ref_no_prefixes')->nullable();
            $table->integer('theme_color')->nullable();

            // Communication settings
            $table->text('email_settings')->nullable();
            $table->text('sms_settings')->nullable();
            $table->text('common_settings')->nullable();
            $table->text('custom_labels')->nullable();
            $table->text('contact_fields')->nullable();
            $table->text('ref_no_starting_number')->nullable();

            // Reward points settings
            $table->boolean('enable_rp')->default(0);
            $table->string('rp_name')->nullable();
            $table->decimal('amount_for_unit_rp', 22, 4)->default(1);
            $table->decimal('min_order_total_for_rp', 22, 4)->default(1);
            $table->integer('max_rp_per_order')->nullable();
            $table->decimal('redeem_amount_per_unit_rp', 22, 4)->default(1);
            $table->decimal('min_order_total_for_redeem', 22, 4)->default(1);
            $table->integer('min_redeem_point')->nullable();
            $table->integer('max_redeem_point')->nullable();
            $table->integer('rp_expiry_period')->nullable();
            $table->enum('rp_expiry_type', ['month', 'year'])->default('year');

            $table->string('reg_no')->nullable();
            $table->string('font_size')->nullable();
            $table->string('font_style')->nullable();

            $table->boolean('popup_load_save_data')->default(0);
            $table->boolean('day_end_enable')->default(0);
            $table->boolean('enable_line_discount')->default(0);
            $table->boolean('duplicate_orders_allowed')->default(0);
            $table->boolean('show_for_customers')->default(0);
            $table->text('business_categories')->nullable();

            $table->string('owner_email')->nullable();
            $table->string('owner_firstname')->nullable();
            $table->string('owner_lastname')->nullable();
            $table->string('owner_phone')->nullable();

            // Link to tenant user after creation (canonical reference)
            $table->uuid('owner_user_uuid')->nullable();

            // Invite tracking
            $table->timestamp('owner_invite_sent_at')->nullable();
            $table->timestamp('owner_activated_at')->nullable();

            // Indexes for performance
            $table->index('owner_email');
            $table->index('owner_user_uuid');

            $table->timestamps();

            $table->index('owner_id');
            $table->index('tenant_id');
            $table->index('package_id');
        });

        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('location_id')->nullable();
            $table->string('landmark')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('invoice_scheme_id')->nullable();
            $table->string('invoice_layout_id')->nullable();
            $table->boolean('print_receipt_on_invoice')->default(1);
            $table->text('receipt_printer_type')->nullable();
            $table->string('printer_id')->nullable();
            $table->string('mobile')->nullable();
            $table->string('alternate_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
        Schema::dropIfExists('businesses');
    }
};

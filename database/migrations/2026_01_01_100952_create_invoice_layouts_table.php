<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_layouts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->text('header_text')->nullable();
            $table->integer('font_size')->nullable();
            $table->unsignedInteger('business_id');
            $table->string('invoice_heading', 191)->nullable();
            $table->integer('header_font_size')->nullable();
            $table->integer('footer_font_size')->nullable();
            $table->string('invoice_no_prefix', 191)->nullable();
            $table->string('quotation_no_prefix', 191)->nullable();
            $table->string('invoice_heading_not_paid', 191)->nullable();
            $table->string('sub_heading_line1', 191)->nullable();
            $table->string('sub_heading_line2', 191)->nullable();
            $table->string('sub_heading_line3', 191)->nullable();
            $table->string('sub_heading_line4', 191)->nullable();
            $table->string('sub_heading_line5', 191)->nullable();
            $table->string('invoice_heading_paid', 191)->nullable();
            $table->string('quotation_heading', 191)->nullable();
            $table->string('sub_total_label', 191)->nullable();
            $table->string('discount_label', 191)->nullable();
            $table->string('tax_label', 191)->nullable();
            $table->string('total_label', 191)->nullable();
            $table->string('total_due_label', 191)->nullable();
            $table->string('paid_label', 191)->nullable();
            $table->tinyInteger('show_client_id')->default(0);
            $table->string('client_id_label', 191)->nullable();
            $table->string('client_tax_label', 191)->nullable();
            $table->string('date_label', 191)->nullable();
            $table->string('date_time_format', 191)->nullable();
            $table->tinyInteger('show_time')->default(1);
            $table->tinyInteger('show_brand')->default(0);
            $table->tinyInteger('show_sku')->default(1);
            $table->tinyInteger('show_cat_code')->default(1);
            $table->tinyInteger('show_expiry')->default(0);
            $table->tinyInteger('show_lot')->default(0);
            $table->tinyInteger('show_image')->default(0);
            $table->tinyInteger('show_sale_description')->default(0);
            $table->string('sales_person_label', 191)->nullable();
            $table->tinyInteger('show_sales_person')->default(0);
            $table->string('table_product_label', 191)->nullable();
            $table->string('table_qty_label', 191)->nullable();
            $table->string('table_unit_price_label', 191)->nullable();
            $table->string('table_subtotal_label', 191)->nullable();
            $table->string('cat_code_label', 191)->nullable();
            $table->string('logo', 191)->nullable();
            $table->tinyInteger('show_logo')->default(0);
            $table->tinyInteger('show_business_name')->default(0);
            $table->tinyInteger('show_location_name')->default(1);
            $table->tinyInteger('show_landmark')->default(1);
            $table->tinyInteger('show_city')->default(1);
            $table->tinyInteger('show_state')->default(1);
            $table->tinyInteger('show_zip_code')->default(1);
            $table->tinyInteger('show_country')->default(1);
            $table->tinyInteger('show_mobile')->default(1);
            $table->tinyInteger('show_alternate_number')->default(0);
            $table->tinyInteger('show_email')->default(0);
            $table->tinyInteger('show_tax_1')->default(1);
            $table->tinyInteger('show_tax_2')->default(0);
            $table->tinyInteger('show_barcode')->default(0);
            $table->tinyInteger('show_payments')->default(0);
            $table->tinyInteger('show_customer')->default(0);
            $table->string('customer_label', 191)->nullable();
            $table->tinyInteger('show_reward_point')->default(0);
            $table->string('highlight_color', 10)->nullable();
            $table->text('footer_text')->nullable();
            $table->text('module_info')->nullable();
            $table->text('common_settings')->nullable();
            $table->tinyInteger('is_default')->default(0);
            $table->string('design', 255)->nullable()->default('classic');
            $table->string('cn_heading', 191)->nullable();
            $table->string('cn_no_label', 191)->nullable();
            $table->string('cn_amount_label', 191)->nullable();
            $table->text('table_tax_headings')->nullable();
            $table->tinyInteger('show_previous_bal')->default(0);
            $table->string('prev_bal_label', 191)->nullable();
            $table->string('change_return_label', 191)->nullable();
            $table->text('product_custom_fields')->nullable();
            $table->text('contact_custom_fields')->nullable();
            $table->text('location_custom_fields')->nullable();
            $table->string('header_align', 20)->nullable();
            $table->integer('logo_height')->nullable();
            $table->integer('logo_margin_top')->nullable();
            $table->integer('logo_margin_bottom')->nullable();
            $table->integer('logo_width')->nullable();
            $table->integer('is_system')->default(0);
            $table->timestamps();
            
            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_layouts');
    }
};

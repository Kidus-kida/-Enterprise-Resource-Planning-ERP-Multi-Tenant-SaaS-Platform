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
        Schema::create('business', function (Blueprint $table) {
            $table->id();
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
            $table->timestamps();

            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business');
    }
};

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('weight_excess_loss_applicable')->default(0);
            $table->unsignedBigInteger('business_id');
            $table->string('type')->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->json('sub_unit_ids')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('sub_category_id')->nullable();
            $table->unsignedInteger('tax')->nullable();
            $table->string('tax_type')->default('exclusive');
            $table->boolean('enable_stock')->default(0);
            $table->decimal('alert_quantity', 22, 4)->nullable();
            $table->string('sku');
            $table->string('barcode_type')->default('C128');
            $table->boolean('expiry_enabled')->default(0);
            $table->integer('expiry_period')->nullable();
            $table->string('expiry_period_type')->nullable();
            $table->boolean('enable_sr_no')->default(0);
            $table->decimal('weight', 22, 4)->nullable();
            $table->text('product_description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('min_sell_price', 22, 4)->default(0);
            $table->unsignedInteger('warranty_id')->nullable();
            $table->boolean('is_inactive')->default(0);
            $table->boolean('not_for_selling')->default(0);
            $table->string('image_url')->nullable();
            $table->string('product_custom_field1')->nullable();
            $table->string('product_custom_field2')->nullable();
            $table->string('product_custom_field3')->nullable();
            $table->string('product_custom_field4')->nullable();
            $table->integer('created_by')->unsigned();
            $table->string('woocommerce_media_id')->nullable();
            $table->boolean('woocommerce_disable_sync')->default(0);
            $table->integer('woocommerce_product_id')->nullable();
            $table->string('woocommerce_sku')->nullable();
            $table->unsignedInteger('secondary_unit_id')->nullable();
            $table->boolean('semi_finished')->default(0);
            $table->string('disabled_in')->nullable();
            $table->unsignedInteger('repair_model_id')->nullable();
            $table->boolean('vat_claimed')->default(0);
            $table->integer('stock_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('category_id');
            $table->index('sub_category_id');
            $table->index('brand_id');
            $table->index('unit_id');
            $table->index('sku');
        });

        Schema::create('variation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->integer('created_by');
            $table->timestamps();

            $table->index('business_id');
        });

        Schema::create('variation_value_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('variation_template_id');
            $table->timestamps();

            $table->index('variation_template_id');
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->string('variation_template_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->string('sub_sku');
            $table->unsignedInteger('product_variation_id');
            $table->decimal('default_purchase_price', 22, 4)->nullable();
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->nullable();
            $table->decimal('sell_price_inc_tax', 22, 4)->nullable();
            $table->string('combo_variations')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('product_variation_id');
            $table->index('sub_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variations');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('variation_value_templates');
        Schema::dropIfExists('variation_templates');
        Schema::dropIfExists('products');
    }
};

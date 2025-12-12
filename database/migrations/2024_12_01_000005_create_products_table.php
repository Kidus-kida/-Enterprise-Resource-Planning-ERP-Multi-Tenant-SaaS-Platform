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
            $table->timestamps();

            $table->index('business_id');
            $table->index('category_id');
            $table->index('sub_category_id');
            $table->index('brand_id');
            $table->index('unit_id');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('product_variation_id');
            $table->unsignedInteger('variation_id');
            $table->unsignedInteger('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();

            $table->index(['product_id', 'variation_id', 'location_id'], 'product_variation_location_index');
        });

        Schema::create('product_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->string('rack')->nullable();
            $table->string('row')->nullable();
            $table->string('position')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('exp_date')->nullable();
            $table->date('mfg_date')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'location_id', 'product_id'], 'product_location_index');
        });

        Schema::create('selling_price_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('business_id');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
        });

        Schema::create('variation_group_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('variation_id');
            $table->decimal('price_inc_tax', 22, 4);
            $table->unsignedInteger('price_group_id');
            $table->timestamps();

            $table->index(['variation_id', 'price_group_id']);
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index('business_id');
            $table->index('location_id');
        });

        Schema::create('system', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('variation_group_prices');
        Schema::dropIfExists('selling_price_groups');
        Schema::dropIfExists('product_locations');
        Schema::dropIfExists('variation_location_details');
    }
};

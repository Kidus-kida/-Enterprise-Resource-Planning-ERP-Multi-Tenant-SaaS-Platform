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
        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->string('sub_sku');
            $table->unsignedBigInteger('product_variation_id');
            $table->string('variation_value_id')->nullable();
            $table->decimal('default_purchase_price', 22, 4)->nullable();
            $table->decimal('dpp_inc_tax', 22, 4)->default(0);
            $table->decimal('profit_percent', 22, 4)->default(0);
            $table->decimal('default_sell_price', 22, 4)->nullable();
            $table->decimal('sell_price_inc_tax', 22, 4)->nullable();
            $table->integer('created_by')->unsigned();
            $table->string('combo_variations')->nullable();
            $table->softDeletes();
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
    }
};

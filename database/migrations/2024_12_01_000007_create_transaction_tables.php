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
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id');
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->decimal('line_discount_amount', 22, 4)->default(0);
            $table->string('line_discount_type')->nullable();
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedInteger('tax_id')->nullable();
            $table->decimal('sell_line_note', 22, 4)->nullable();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('product_id');
            $table->index('variation_id');
        });

        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sell_line_id')->nullable();
            $table->unsignedBigInteger('purchase_line_id');
            $table->unsignedBigInteger('stock_adjustment_line_id')->nullable();
            $table->decimal('quantity', 22, 4);
            $table->decimal('qty_returned', 22, 4)->default(0);
            $table->timestamps();

            $table->index('sell_line_id', 'tslpl_sell_line_index');
            $table->index('purchase_line_id', 'tslpl_purchase_line_index');
            $table->index('stock_adjustment_line_id', 'tslpl_stock_adj_index');
        });

        Schema::create('reference_counts', function (Blueprint $table) {
            $table->id();
            $table->string('ref_type')->unique();
            $table->unsignedBigInteger('ref_count')->default(0);
            $table->integer('business_id')->default(1);
            $table->timestamps();

            $table->index(['ref_type', 'business_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_counts');
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::dropIfExists('transaction_sell_lines');
    }
};

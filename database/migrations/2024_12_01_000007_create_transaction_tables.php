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
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
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
            $table->unsignedBigInteger('parent_sell_line_id')->nullable();
            $table->string('children_type')->nullable();
            $table->decimal('unit_price_before_discount', 22, 4)->default(0);
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedBigInteger('sub_unit_id')->nullable()->comment('Unit ID for the sold quantity');
            $table->unsignedBigInteger('res_service_staff_id')->nullable();
            $table->unsignedBigInteger('lot_no_line_id')->nullable();
            $table->string('res_line_order_status')->nullable();
            $table->decimal('weight_loss', 22, 4)->nullable();
            $table->decimal('weight_excess', 22, 4)->nullable();
            $table->decimal('last_purchased_price', 22, 4)->nullable();
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('product_id');
            $table->index('variation_id');
        });

        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
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
            $table->unsignedBigInteger('company_id')->nullable()->index();
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

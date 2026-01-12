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
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_sell_lines', 'line_discount_type')) {
                $table->string('line_discount_type')->nullable()->comment('fixed or percentage')->after('unit_price');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'line_discount_amount')) {
                $table->decimal('line_discount_amount', 22, 4)->default(0)->after('line_discount_type');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'unit_price_before_discount')) {
                $table->decimal('unit_price_before_discount', 22, 4)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'sell_line_note')) {
                $table->text('sell_line_note')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'sub_unit_id')) {
                $table->unsignedBigInteger('sub_unit_id')->nullable()->after('product_id')->comment('Unit ID for the sold quantity');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'discount_id')) {
                $table->unsignedBigInteger('discount_id')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'res_service_staff_id')) {
                $table->unsignedBigInteger('res_service_staff_id')->nullable();
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'res_line_order_status')) {
                $table->string('res_line_order_status')->nullable();
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'lot_no_line_id')) {
                $table->unsignedBigInteger('lot_no_line_id')->nullable();
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'weight_loss')) {
                $table->decimal('weight_loss', 22, 4)->nullable();
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'weight_excess')) {
                $table->decimal('weight_excess', 22, 4)->nullable();
            }
            if (!Schema::hasColumn('transaction_sell_lines', 'last_purchased_price')) {
         	    $table->decimal('last_purchased_price', 22, 4)->nullable();
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            $table->dropColumn([
                'line_discount_type',
                'line_discount_amount',
                'unit_price_before_discount',
                'sell_line_note',
                'sub_unit_id',
                'discount_id',
                'res_service_staff_id',
                'res_line_order_status',
                'lot_no_line_id',
                'weight_loss',
                'weight_excess',
                'last_purchased_price'
            ]);
        });
    }
};

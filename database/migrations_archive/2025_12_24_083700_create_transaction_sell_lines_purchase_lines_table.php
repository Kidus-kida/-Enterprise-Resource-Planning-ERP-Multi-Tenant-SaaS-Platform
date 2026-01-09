<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('transaction_sell_lines_purchase_lines')) {
            Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sell_line_id')->unsigned()->nullable()->index('tslpl_sell_line_id_idx');
                $table->integer('stock_adjustment_line_id')->unsigned()->nullable()->index('tslpl_stock_adj_line_id_idx');
                $table->integer('purchase_line_id')->unsigned()->index('tslpl_purchase_line_id_idx');
                $table->decimal('quantity', 22, 4);
                $table->decimal('qty_returned', 22, 4)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
    }
};

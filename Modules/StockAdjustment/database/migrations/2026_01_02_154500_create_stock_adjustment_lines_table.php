<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stock_adjustment_lines')) {
            Schema::create('stock_adjustment_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transaction_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variation_id');
                $table->decimal('quantity', 22, 4);
                $table->decimal('unit_price', 22, 4);
                $table->string('type', 20)->nullable();
                $table->string('stock_adjustment_type', 20)->nullable();
                $table->integer('removed_purchase_line')->nullable();
                $table->integer('lot_no_line_id')->nullable();
                $table->unsignedBigInteger('tank_id')->nullable();
                $table->unsignedBigInteger('inventory_adjustment_account')->nullable();
                $table->timestamps();

                $table->index('transaction_id');
                $table->index('product_id');
                $table->index('variation_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_adjustment_lines');
    }
};

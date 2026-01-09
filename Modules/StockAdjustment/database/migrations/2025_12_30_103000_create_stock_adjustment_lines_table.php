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
        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->unsigned()->index();
            $table->integer('product_id')->unsigned()->index();
            $table->integer('variation_id')->unsigned()->index();
            $table->decimal('quantity', 22, 4);
            $table->decimal('unit_price', 22, 4)->nullable();
            $table->string('type', 20)->nullable();
            $table->integer('removed_purchase_line_id')->nullable();
            $table->integer('lot_no_line_id')->nullable();
            $table->integer('tank_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_lines');
    }
};

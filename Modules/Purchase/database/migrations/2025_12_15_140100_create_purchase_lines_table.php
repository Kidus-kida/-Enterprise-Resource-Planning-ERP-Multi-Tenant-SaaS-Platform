<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            
            $table->decimal('quantity', 22, 4);
            $table->decimal('pp_without_discount', 22, 4)->default(0)->comment('Purchase price before inline discount');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('purchase_price', 22, 4);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            
            $table->string('lot_number')->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('exp_date')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_lines');
    }
};

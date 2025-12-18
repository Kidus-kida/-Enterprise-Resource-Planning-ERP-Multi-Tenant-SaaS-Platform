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
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();

            // Add other potential fields based on usage if needed, but these are core for relation
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaction_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_sell_lines');
    }
};

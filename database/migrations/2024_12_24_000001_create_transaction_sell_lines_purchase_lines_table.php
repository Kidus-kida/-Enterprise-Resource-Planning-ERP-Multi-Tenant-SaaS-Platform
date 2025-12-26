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
        if (!Schema::hasTable('transaction_sell_lines_purchase_lines')) {
            Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sell_line_id')->nullable();
                $table->unsignedBigInteger('purchase_line_id')->default(0);
                $table->unsignedBigInteger('stock_adjustment_line_id')->nullable();
                $table->decimal('quantity', 22, 4)->default(0);
                $table->timestamps();
                
                $table->index('sell_line_id');
                $table->index('purchase_line_id');
                $table->index('stock_adjustment_line_id');
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

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
        Schema::create('variation_group_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variation_id');
            $table->unsignedBigInteger('price_group_id');
            $table->decimal('price_inc_tax', 22, 4);
            $table->integer('created_by')->unsigned();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['variation_id', 'price_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_group_prices');
    }
};

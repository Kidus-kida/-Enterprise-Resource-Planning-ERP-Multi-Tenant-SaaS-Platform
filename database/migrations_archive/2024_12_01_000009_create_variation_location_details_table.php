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
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variation_id');
            $table->unsignedBigInteger('variation_id');
            $table->unsignedBigInteger('location_id');
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['product_id', 'variation_id', 'location_id'], 'product_variation_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_location_details');
    }
};

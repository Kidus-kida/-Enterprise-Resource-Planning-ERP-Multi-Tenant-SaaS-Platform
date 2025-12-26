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
        Schema::create('supplier_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->index(); // Contact ID
            $table->unsignedBigInteger('product_id')->index();  // Product ID (assuming products table exists, or will exists)
            $table->timestamps();
            $table->softDeletes();
            
            // $table->unique(['supplier_id', 'product_id']); // Prevent duplicate mappings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_mappings');
    }
};

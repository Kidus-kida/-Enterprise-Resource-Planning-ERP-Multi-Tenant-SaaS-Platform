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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->decimal('salary_from', 10, 2);
            $table->decimal('salary_to', 10, 2)->nullable(); // can be null for higher value(>14000)"
            $table->decimal('percentage', 5, 2); 
            $table->decimal('deducted_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};

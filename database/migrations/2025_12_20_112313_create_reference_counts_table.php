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
        Schema::create('reference_counts', function (Blueprint $table) {
            $table->id();
            $table->string('ref_type')->unique();
            $table->unsignedBigInteger('ref_count')->default(0);
            $table->Integer('business_id')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_counts');
    }
};

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
        Schema::create('merged_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->date('date_and_time');
            $table->unsignedBigInteger('category_id');
            $table->string('merged_sub_category_name');
            $table->json('sub_categories')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merged_sub_categories');
    }
};

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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variation_template_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('product_id');
            $table->boolean('is_dummy')->default(1);
            $table->integer('created_by')->unsigned();
            $table->softDeletes();
            $table->timestamps();

            $table->index('product_id');
            $table->index('variation_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};

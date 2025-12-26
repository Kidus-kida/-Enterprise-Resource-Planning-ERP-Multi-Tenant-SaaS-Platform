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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->string('short_code')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->integer('category_type')->nullable();
            $table->text('description')->nullable();
            $table->integer('created_by')->unsigned();
            $table->softDeletes();
            $table->timestamps();

            $table->index('business_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

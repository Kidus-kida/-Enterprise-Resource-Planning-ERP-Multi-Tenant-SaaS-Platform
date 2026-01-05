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
        if (!Schema::hasTable('stock_adjustment_settings')) {
            Schema::create('stock_adjustment_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id');
            $table->timestamp('date')->nullable();
            $table->string('adjustment_type', 30);
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('account_to_link')->nullable();
            $table->integer('stock_group')->nullable();
            $table->integer('stock_account')->nullable();
            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_settings');
    }
};

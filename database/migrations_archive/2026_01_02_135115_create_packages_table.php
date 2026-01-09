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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 22, 4)->default(0);
            $table->unsignedInteger('currency_id')->nullable();
            $table->enum('interval', ['days', 'months', 'years'])->default('months');
            $table->integer('interval_count')->default(1);
            $table->integer('trial_days')->default(0);
            
            // Limits
            $table->integer('location_count')->default(0);
            $table->integer('user_count')->default(0);
            $table->integer('product_count')->default(0);
            $table->integer('invoice_count')->default(0);
            
            // Module Permissions (JSON for flexibility)
            $table->json('custom_permissions')->nullable();
            
            // Settings
            $table->boolean('is_active')->default(1);
            $table->boolean('is_private')->default(0);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

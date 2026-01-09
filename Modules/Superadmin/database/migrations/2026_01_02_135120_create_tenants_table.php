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
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->string('id')->primary(); // Tenant ID (e.g., 'tenant_abc')
                $table->unsignedBigInteger('business_id')->unique()->nullable();
                $table->string('database_name')->nullable(); // Tenant database name
                $table->json('data')->nullable(); // Additional tenant metadata
                $table->timestamps();
                
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

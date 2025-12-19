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
        Schema::create('account_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('key');
            $table->date('date')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->decimal('amount', 15, 6)->nullable();
            $table->unsignedBigInteger('at_asset_id')->nullable();
            $table->unsignedBigInteger('at_obe_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            // Indexes
            $table->index('business_id');
            $table->index('account_id');
            $table->index('group_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_settings');
    }
};

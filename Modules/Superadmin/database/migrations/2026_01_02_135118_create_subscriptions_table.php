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
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('package_id')->nullable();
                
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                
                // Package snapshot at subscription time
                $table->json('package_details')->nullable();
                
                // Module activation tracking
                $table->json('module_activation_details')->nullable();
                
                // Payment info
                $table->string('paid_via')->nullable();
                $table->string('payment_transaction_id')->nullable();
                
                $table->enum('status', ['approved', 'waiting', 'declined'])->default('waiting');
                
                $table->unsignedBigInteger('created_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

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
        Schema::create('leave_accrual_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard Annual Leave Accrual"
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            
            // Accrual Settings
            $table->enum('accrual_frequency', ['monthly', 'yearly', 'per_pay_period'])->default('monthly');
            $table->decimal('accrual_rate', 8, 2); // e.g., 1.25 days per month
            $table->integer('max_accrual_days')->nullable(); // Maximum days that can accrue
            
            // Eligibility
            $table->integer('waiting_period_days')->default(0); // Days before accrual starts
            $table->boolean('prorate_on_join')->default(true); // Prorate for mid-period joins
            
            // Carryover Rules
            $table->boolean('allow_carryover')->default(true);
            $table->integer('max_carryover_days')->nullable(); // Max days to carry to next year
            $table->date('carryover_expiry_date')->nullable(); // When carried days expire
            
            // Negative Balance
            $table->boolean('allow_negative_balance')->default(false);
            $table->integer('max_negative_days')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('leave_type_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_accrual_plans');
    }
};

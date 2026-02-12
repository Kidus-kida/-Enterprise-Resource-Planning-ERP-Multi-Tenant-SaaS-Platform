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
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->foreignId('accrual_plan_id')->nullable()->constrained('leave_accrual_plans')->onDelete('set null');
            
            // Period
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            
            // Balance Tracking
            $table->decimal('opening_balance', 8, 2)->default(0); // Balance at start of period
            $table->decimal('allocated_days', 8, 2)->default(0); // Total allocated for period
            $table->decimal('accrued_days', 8, 2)->default(0); // Days accrued so far
            $table->decimal('used_days', 8, 2)->default(0); // Days used
            $table->decimal('pending_days', 8, 2)->default(0); // Days in pending requests
            $table->decimal('available_days', 8, 2)->default(0); // Current available balance
            $table->decimal('carried_forward', 8, 2)->default(0); // Days carried from previous year
            
            // Metadata
            $table->date('last_accrual_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('allocation_type', ['manual', 'accrual', 'carryover', 'bonus', 'adjustment'])->default('manual');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->boolean('is_manual_allocation')->default(false); // Manual vs auto-accrued
            $table->foreignId('allocated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'leave_type_id']);
            $table->index('period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_allocations');
    }
};

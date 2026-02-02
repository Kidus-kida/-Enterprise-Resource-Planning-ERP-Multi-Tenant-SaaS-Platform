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
        Schema::table('leave_types', function (Blueprint $table) {
            // Accrual Configuration
            $table->boolean('uses_accrual')->default(false)->after('description');
            $table->foreignId('default_accrual_plan_id')->nullable()->after('uses_accrual')
                  ->constrained('leave_accrual_plans')->onDelete('set null');
            
            // Leave Behavior
            $table->boolean('requires_attachment')->default(false)->after('default_accrual_plan_id');
            $table->integer('min_days_notice')->default(0)->after('requires_attachment'); // Days notice required
            $table->integer('max_consecutive_days')->nullable()->after('min_days_notice'); // Max days in one request
            $table->boolean('allow_half_day')->default(true)->after('max_consecutive_days');
            //$table->boolean('is_paid')->default(true)->after('allow_half_day'); // Add is_paid field
            
            // Approval Settings
            $table->boolean('requires_approval')->default(true)->after('allow_half_day');
            $table->integer('approval_levels')->default(1)->after('requires_approval'); // Number of approval levels
            $table->boolean('auto_approve_if_balance')->default(false)->after('approval_levels');
            
            // Display & Ordering
            $table->string('color', 7)->default('#0d6efd')->after('auto_approve_if_balance'); // Hex color for UI
            $table->integer('sort_order')->default(0)->after('color');
            $table->boolean('is_active')->default(true)->after('sort_order');
            
            // Soft deletes
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uses_accrual',
                'default_accrual_plan_id',
                'requires_attachment',
                'min_days_notice',
                'max_consecutive_days',
                'allow_half_day',
                'requires_approval',
                'approval_levels',
                'auto_approve_if_balance',
                'color',
                'sort_order',
                'is_active'
            ]);
        });
    }
};

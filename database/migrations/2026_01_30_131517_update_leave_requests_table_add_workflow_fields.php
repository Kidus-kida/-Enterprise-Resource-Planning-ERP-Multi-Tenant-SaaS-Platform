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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Enhanced workflow fields
            $table->enum('request_type', ['full_day', 'half_day', 'hourly'])->default('full_day')->after('multiple_day');
            $table->decimal('total_hours', 8, 2)->nullable()->after('request_type'); // For hourly requests
            $table->decimal('total_days', 8, 2)->default(0)->after('total_hours'); // Add total_days field
            
            // Approval Workflow
            $table->integer('current_approval_level')->default(1)->after('status');
            $table->integer('required_approval_levels')->default(1)->after('current_approval_level');
            $table->json('approval_chain')->nullable()->after('required_approval_levels'); // Track all approvers
            $table->timestamp('approved_at')->nullable()->after('approval_chain');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            
            // Cancellation
            $table->boolean('is_cancelled')->default(false)->after('reject_reason');
            $table->integer('cancelled_by')->nullable()->after('is_cancelled');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            
            // Metadata
            $table->boolean('is_emergency')->default(false)->after('cancellation_reason');
            $table->text('admin_notes')->nullable()->after('is_emergency');
            
            // Soft deletes
            $table->softDeletes()->after('updated_at');
            
            // Indexes - only add new ones that don't exist
            $table->index('current_approval_level');
            $table->index('is_cancelled');
            // Note: employee_id, leave_type_id, and status indexes already exist
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['current_approval_level']);
            $table->dropIndex(['is_cancelled']);
            
            $table->dropColumn([
                'request_type',
                'total_hours',
                'total_days',
                'current_approval_level',
                'required_approval_levels',
                'approval_chain',
                'approved_at',
                'rejected_at',
                'is_cancelled',
                'cancelled_by',
                'cancelled_at',
                'cancellation_reason',
                'is_emergency',
                'admin_notes'
            ]);
        });
    }
};

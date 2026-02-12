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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('type_name');
            $table->string('description')->nullable();
            
            // Time Off Logic
            $table->enum('duration_type', ['day', 'half_day', 'hours'])->default('day');
            $table->enum('count_as', ['absence', 'worked_time'])->default('absence');
            $table->string('leave_allowed_interval')->nullable(); // e.g. 'monthly', 'yearly'
            
            // Availability & Visibility
            $table->boolean('ignore_public_holidays')->default(false);
            $table->boolean('hide_on_dashboard')->default(false);
            $table->boolean('eligible_for_accrual')->default(false);

            // Notification
            $table->boolean('notify_hr')->default(false);
            $table->json('hr_notification_recipients')->nullable();

            // Allocation Requests
            $table->boolean('requires_allocation')->default(true);
            $table->boolean('employee_requests_allowed')->default(false);
            $table->integer('allocation_approval_levels')->default(1);

            // Leave Behavior (Requests)
            $table->boolean('requires_attachment')->default(false);
            $table->integer('min_days_notice')->default(0); 
            $table->integer('max_consecutive_days')->nullable(); 
            $table->boolean('allow_half_day')->default(true);
            $table->boolean('is_paid')->default(true);

            // Request Approval Settings
            $table->boolean('requires_approval')->default(true);
            $table->integer('approval_levels')->default(1); 
            $table->boolean('auto_approve_if_balance')->default(false);
            
            // Balance Settings
            $table->boolean('allow_negative_balance')->default(false); // Negative Cap
            $table->integer('max_negative_balance')->default(0);
            $table->boolean('can_carry_forward')->default(false);
            $table->integer('max_carry_forward')->default(0); 
            $table->integer('carry_forward_expiry')->nullable(); 

            // Display & Ordering
            $table->string('color', 7)->default('#0d6efd'); 
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Soft deletes
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->integer('employee_id');
            $table->integer('leave_type_id');
            $table->date('leave_start_date')->nullable();
            $table->date('leave_end_date')->nullable();
            $table->string('request_reason')->nullable();
            $table->longText('attachements')->nullable();
            $table->string('half_day')->nullable();
            $table->integer('multiple_day')->default(0);
            $table->string('reject_reason')->nullable();
            $table->integer('attended_by')->nullable();
            $table->string('status')->default('pending');
            $table->enum('request_type', ['full_day', 'half_day', 'hourly'])->default('full_day');
            $table->decimal('total_hours', 8, 2)->nullable(); // For hourly requests
            $table->decimal('total_days', 8, 2)->default(0); // Add total_days field

            // Approval Workflow
            $table->integer('current_approval_level')->default(1);
            $table->integer('required_approval_levels')->default(1);
            $table->json('approval_chain')->nullable(); // Track all approvers
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Cancellation
            $table->boolean('is_cancelled')->default(false);
            $table->integer('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Metadata
            $table->boolean('is_emergency')->default(false);
            $table->text('admin_notes')->nullable();

            // Soft deletes
            $table->softDeletes();

            // Indexes - only add new ones that don't exist
            $table->index('current_approval_level');
            $table->index('is_cancelled');
            $table->timestamps();
        });

        Schema::create('anunal_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->integer('employee_id');
            $table->decimal('current_year', 8, 2)->default(0.00);
            $table->decimal('previous_year', 8, 2)->default(0.00);
            $table->string('year_bpy')->default('2025');
            $table->decimal('per_month', 8, 2)->default(0.00);
            $table->integer('per_year')->default(0);
            $table->integer('total_anunal_leave')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anunal_leaves');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
    }
};

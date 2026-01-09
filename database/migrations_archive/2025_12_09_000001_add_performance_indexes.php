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
        // Users table indexes
        $this->safeAddIndex('users', 'type', 'users_type_index');
        $this->safeAddIndex('users', 'is_active', 'users_is_active_index');
        $this->safeAddIndex('users', 'created_at', 'users_created_at_index');
        $this->safeAddIndex('users', ['type', 'is_active'], 'users_type_is_active_index');

        // Employee details table indexes
        $this->safeAddIndex('employee_details', 'department_id', 'employee_details_department_id_index');
        $this->safeAddIndex('employee_details', 'designation_id', 'employee_details_designation_id_index');

        // Attendances table indexes
        $this->safeAddIndex('attendances', 'startDate', 'attendances_start_date_index');
        $this->safeAddIndex('attendances', ['user_id', 'created_at'], 'attendances_user_created_index');

        // Leave requests table indexes
        $this->safeAddIndex('leave_requests', 'status', 'leave_requests_status_index');
        $this->safeAddIndex('leave_requests', ['employee_id', 'status'], 'leave_requests_employee_status_index');
        $this->safeAddIndex('leave_requests', ['leave_start_date', 'leave_end_date'], 'leave_requests_dates_index');

        // Payroll batches table indexes
        if (Schema::hasTable('payroll_batches')) {
            $this->safeAddIndex('payroll_batches', 'status', 'payroll_batches_status_index');
            $this->safeAddIndex('payroll_batches', 'period_start', 'payroll_batches_period_start_index');
        }

        // Tickets table indexes
        $this->safeAddIndex('tickets', 'user_id', 'tickets_user_id_index');
        $this->safeAddIndex('tickets', 'created_by', 'tickets_created_by_index');
        $this->safeAddIndex('tickets', 'status', 'tickets_status_index');
    }

    /**
     * Safely add an index if it doesn't exist.
     */
    protected function safeAddIndex(string $table, string|array $columns, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index likely exists or table doesn't exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->safeDropIndex('users', 'users_type_index');
        $this->safeDropIndex('users', 'users_is_active_index');
        $this->safeDropIndex('users', 'users_created_at_index');
        $this->safeDropIndex('users', 'users_type_is_active_index');

        $this->safeDropIndex('employee_details', 'employee_details_department_id_index');
        $this->safeDropIndex('employee_details', 'employee_details_designation_id_index');

        $this->safeDropIndex('attendances', 'attendances_start_date_index');
        $this->safeDropIndex('attendances', 'attendances_user_created_index');

        $this->safeDropIndex('leave_requests', 'leave_requests_status_index');
        $this->safeDropIndex('leave_requests', 'leave_requests_employee_status_index');
        $this->safeDropIndex('leave_requests', 'leave_requests_dates_index');

        if (Schema::hasTable('payroll_batches')) {
            $this->safeDropIndex('payroll_batches', 'payroll_batches_status_index');
            $this->safeDropIndex('payroll_batches', 'payroll_batches_period_start_index');
        }

        $this->safeDropIndex('tickets', 'tickets_user_id_index');
        $this->safeDropIndex('tickets', 'tickets_created_by_index');
        $this->safeDropIndex('tickets', 'tickets_status_index');
    }

    /**
     * Safely drop an index.
     */
    protected function safeDropIndex(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Exception $e) {
            // Index likely doesn't exist, continue
        }
    }
};

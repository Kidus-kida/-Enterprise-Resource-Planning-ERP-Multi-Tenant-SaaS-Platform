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
        // Payslips table indexes
        if (Schema::hasTable('payslips')) {
            $this->safeAddIndex('payslips', 'employee_id', 'payslips_employee_id_index');
            $this->safeAddIndex('payslips', 'created_at', 'payslips_created_at_index');
            $this->safeAddIndex('payslips', 'type', 'payslips_type_index');
        }

        // Payslip items table indexes
        if (Schema::hasTable('payslip_items')) {
            $this->safeAddIndex('payslip_items', 'payslip_id', 'payslip_items_payslip_id_index');
        }

        // Assets table indexes
        if (Schema::hasTable('assets')) {
            $this->safeAddIndex('assets', 'user_id', 'assets_user_id_index');
            $this->safeAddIndex('assets', 'created_at', 'assets_created_at_index');
        }

        // Tickets table indexes (additional to existing)
        if (Schema::hasTable('tickets')) {
            $this->safeAddIndex('tickets', 'priority', 'tickets_priority_index');
        }

        // Client details table indexes
        if (Schema::hasTable('client_details')) {
            $this->safeAddIndex('client_details', 'user_id', 'client_details_user_id_index', true);
        }

        // Employee salary details indexes
        if (Schema::hasTable('employee_salary_details')) {
            $this->safeAddIndex('employee_salary_details', 'employee_detail_id', 'emp_salary_details_emp_detail_id_index');
        }

        // Awards table indexes
        if (Schema::hasTable('awards')) {
            $this->safeAddIndex('awards', 'employee_id', 'awards_employee_id_index');
            $this->safeAddIndex('awards', 'created_at', 'awards_created_at_index');
        }

        // Folders table indexes
        if (Schema::hasTable('folders')) {
            $this->safeAddIndex('folders', 'created_by', 'folders_created_by_index');
        }

        // Files table indexes
        if (Schema::hasTable('files')) {
            $this->safeAddIndex('files', 'folder_id', 'files_folder_id_index');
            $this->safeAddIndex('files', 'created_by', 'files_created_by_index');
        }
    }

    /**
     * Safely add an index if it doesn't exist.
     */
    protected function safeAddIndex(string $table, string|array $columns, string $indexName, bool $unique = false): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName, $unique) {
                if ($unique) {
                    $table->unique($columns, $indexName);
                } else {
                    $table->index($columns, $indexName);
                }
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
        if (Schema::hasTable('payslips')) {
            $this->safeDropIndex('payslips', 'payslips_employee_id_index');
            $this->safeDropIndex('payslips', 'payslips_created_at_index');
            $this->safeDropIndex('payslips', 'payslips_type_index');
        }

        if (Schema::hasTable('payslip_items')) {
            $this->safeDropIndex('payslip_items', 'payslip_items_payslip_id_index');
        }

        if (Schema::hasTable('assets')) {
            $this->safeDropIndex('assets', 'assets_user_id_index');
            $this->safeDropIndex('assets', 'assets_created_at_index');
        }

        if (Schema::hasTable('tickets')) {
            $this->safeDropIndex('tickets', 'tickets_priority_index');
        }

        if (Schema::hasTable('client_details')) {
            $this->safeDropIndex('client_details', 'client_details_user_id_index');
        }

        if (Schema::hasTable('employee_salary_details')) {
            $this->safeDropIndex('employee_salary_details', 'emp_salary_details_emp_detail_id_index');
        }

        if (Schema::hasTable('awards')) {
            $this->safeDropIndex('awards', 'awards_employee_id_index');
            $this->safeDropIndex('awards', 'awards_created_at_index');
        }

        if (Schema::hasTable('folders')) {
            $this->safeDropIndex('folders', 'folders_created_by_index');
        }

        if (Schema::hasTable('files')) {
            $this->safeDropIndex('files', 'files_folder_id_index');
            $this->safeDropIndex('files', 'files_created_by_index');
        }
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

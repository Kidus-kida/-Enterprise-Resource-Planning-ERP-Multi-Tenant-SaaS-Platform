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
        // Comprehensive list of tables that require company_id
        $tables = [
            'transactions', 'contacts', 'products', 'users', 'transaction_payments',
            'departments', 'designations', 'companies', 'business_locations',
            'estimates', 'invoices', 'expenses', 'projects', 'budgets',
            'tickets',
            // Payroll & Essentials
            'payroll_groups', 'payroll_employees', 'payroll_payrolls', 'payroll_leaves', 'payroll_holidays',
            'payroll_shifts', 'payroll_attendances', 'payroll_allowances', 'payroll_deductions',
            'essentials_todo', 'essentials_messages', 'essentials_allowances', 'essentials_deductions', 'essentials_attendances',
            // All other tables found during deep scan
            'accounts', 'account_groups', 'account_settings', 'account_transactions', 'account_types',
            'anunal_leaves', 'assets', 'asset_issues', 'attendances', 'attendance_timestamps',
            'awards', 'brands', 'budget_categories', 'businesses', 'campaigns', 'cars', 'categories',
            'chat_messages', 'cities', 'client_details', 'contact_groups', 'containers', 'countries',
            'currencies', 'customer_statements', 'customer_statement_details', 'customs_declarations',
            'documents', 'domains', 'drivers', 'dry_ports', 'employee_allowances', 'employee_deductions',
            'employee_details', 'employee_education', 'employee_evaluator', 'employee_salary_details',
            'employee_work_experiences', 'estimate_items', 'expense_budgets', 'files', 'fixed_assets',
            'folders', 'follow_ups', 'holidays', 'hs_codes', 'invoice_items', 'job_batches', 'journals',
            'labels', 'label_task', 'languages', 'leads', 'leave_requests', 'leave_types', 'manual_payments',
            'media', 'member_folder', 'merged_sub_categories', 'modules', 'packages', 'package_addons',
            'payroll_batches', 'payroll_details', 'payroll_settings', 'payroll_tax_brackets', 'payslips',
            'payslip_items', 'performances', 'permissions', 'postdated_cheques', 'product_locations',
            'product_racks', 'product_variations', 'project_leads', 'project_task_boards', 'project_teams',
            'purchase_lines', 'reference_counts', 'revenue_budgets', 'roles', 'selling_price_groups',
            'settings', 'shipments', 'states', 'stock_transfer_requests', 'stores', 'subscriptions',
            'subscription_addons', 'sub_tasks', 'supplier_product_mappings', 'system', 'tasks',
            'task_boards', 'task_comments', 'task_followers', 'task_history', 'taxes', 'tax_calculations',
            'tax_rates', 'tenants', 'ticket_replies', 'timezones', 'transaction_sell_lines',
            'transaction_sell_lines_purchase_lines', 'transfer_shipment', 'transport_trips', 'units',
            'user_family_infos', 'variations', 'variation_group_prices', 'variation_location_details',
            'variation_templates', 'variation_transfers', 'variation_value_templates', 'warranties',
            // Pivot tables without ID
            'cache_locks', 'model_has_permissions', 'model_has_roles', 'password_reset_tokens', 'role_has_permissions'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Check if 'id' exists to decide placement
                    if (Schema::hasColumn($tableName, 'id')) {
                        $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                    } else {
                        $table->unsignedBigInteger('company_id')->nullable()->index();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['transactions', 'contacts', 'products', 'users', 'transaction_payments'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};

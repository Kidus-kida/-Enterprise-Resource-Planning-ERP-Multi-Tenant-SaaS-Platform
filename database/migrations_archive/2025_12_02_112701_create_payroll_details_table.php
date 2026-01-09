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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->onDelete('cascade');
            $table->foreignId('employee_detail_id')->constrained('employee_details')->onDelete('cascade');
            
            // Salary Components
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('taxable_allowances', 10, 2)->default(0);
            $table->decimal('non_taxable_allowances', 10, 2)->default(0);
            
            // Overtime
            $table->decimal('overtime_regular_hours', 5, 2)->default(0);
            $table->decimal('overtime_sunday_hours', 5, 2)->default(0);
            $table->decimal('overtime_holiday_hours', 5, 2)->default(0);
            $table->decimal('overtime_pay', 10, 2)->default(0);
            
            // Gross & Taxable
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('taxable_income', 10, 2)->default(0);
            
            // Deductions
            $table->decimal('income_tax', 10, 2)->default(0);
            $table->decimal('pension_employee', 10, 2)->default(0);
            $table->decimal('pension_employer', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            
            // Net
            $table->decimal('net_salary', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};

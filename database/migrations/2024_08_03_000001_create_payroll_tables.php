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
        Schema::create('employee_salary_details', function (Blueprint $table) {
            // 1. id bigint(20) UNSIGNED AUTO_INCREMENT
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            // 2. employee_detail_id bigint(20) UNSIGNED NULL
            // Assuming this links to an 'employee_details' table. If not, remove the ->constrained part.
            $table->foreignId('employee_detail_id')->nullable()->constrained('employee_details')->nullOnDelete();

            // 3. account_number varchar(255) NULL
            $table->string('account_number')->nullable();

            // 4. basis varchar(255) NULL
            $table->string('basis')->nullable();

            // 5. base_salary double NULL
            $table->double('base_salary')->nullable();

            // 6. payment_method varchar(255) NULL
            $table->string('payment_method')->nullable();

            // 7. pf_contribution tinyint(1) Default 0
            // tinyInteger is used for tinyint. 0 usually represents false/off.
            $table->tinyInteger('pf_contribution')->nullable()->default(0);

            // 8. pf_number varchar(255) NULL
            $table->string('pf_number')->nullable();

            // 9. additional_pf double NULL
            $table->double('additional_pf')->nullable();

            // 10. total_pf_rate double NULL
            $table->double('total_pf_rate')->nullable();

            // 11. esi_contribution tinyint(1) Default 0
            $table->tinyInteger('esi_contribution')->nullable()->default(0);

            // 12. esi_number varchar(255) NULL
            $table->string('esi_number')->nullable();

            // 13. additional_esi_rate double NULL
            $table->double('additional_esi_rate')->nullable();

            // 14. total_additional_esi_rate double NULL
            $table->double('total_additional_esi_rate')->nullable();

            // 15 & 16. created_at and updated_at timestamps NULL
            $table->timestamps();
        });

       
            Schema::create('employee_allowances', function (Blueprint $table) {
                // 1. id bigint(20) UNSIGNED AUTO_INCREMENT
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();

                // 2. employee_detail_id bigint(20) UNSIGNED NULL
                // Links to 'employee_details' table.
                $table->foreignId('employee_detail_id')
                    ->nullable()
                    ->constrained('employee_details')
                    ->nullOnDelete();

                // 3. name varchar(255) NULL
                // Renamed from 'allowance_name' to 'name'
                $table->string('name')->nullable();

                // 4. amount double NULL
                // Changed from 'decimal' to 'double'
                $table->double('amount')->nullable();

                // 5 & 6. created_at and updated_at timestamps NULL
                $table->timestamps();
            });
        

        Schema::create('employee_deductions', function (Blueprint $table) {
            // 1. id bigint(20) UNSIGNED AUTO_INCREMENT
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            // 2. employee_detail_id bigint(20) UNSIGNED NULL
            // Links to 'employee_details' table.
            $table->foreignId('employee_detail_id')
                  ->nullable()
                  ->constrained('employee_details')
                  ->nullOnDelete();

            // 3. name varchar(255) NULL
            // Renamed from 'deduction_name' to 'name'
            $table->string('name')->nullable();

            // 4. amount double NULL
            // Changed from 'decimal' to 'double'
            $table->double('amount')->nullable();

            // 5 & 6. created_at and updated_at timestamps NULL
            $table->timestamps();
        });

       Schema::create('payslips', function (Blueprint $table) {
            // 1. id bigint(20) UNSIGNED AUTO_INCREMENT
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            // 2. ps_id varchar(255) NULL
            $table->string('ps_id')->nullable();

            // 3. title varchar(255) NULL
            $table->string('title')->nullable();

            // 4. employee_detail_id bigint(20) UNSIGNED NULL
            $table->foreignId('employee_detail_id')
                  ->nullable()
                  ->constrained('employee_details')
                  ->nullOnDelete();

            // 5. use_allowance tinyint(1) Default 1
            $table->boolean('use_allowance')->nullable()->default(1);

            // 6. use_deduction tinyint(1) Default 1
            $table->boolean('use_deduction')->nullable()->default(1);

            // 7. payslip_date date NULL
            $table->date('payslip_date')->nullable();

            // 8. type varchar(255) NULL
            $table->string('type')->nullable();

            // 9. weeks varchar(255) NULL
            $table->string('weeks')->nullable();

            // 10. startDate date NULL
            $table->date('startDate')->nullable();

            // 11. endDate date NULL
            $table->date('endDate')->nullable();

            // 12. total_hours varchar(255) NULL
            $table->string('total_hours')->nullable();

            // 13. net_pay double NULL
            $table->double('net_pay')->nullable();

            // 14 & 15. created_at and updated_at timestamps NULL
            $table->timestamps();
        });

        Schema::create('payslip_items', function (Blueprint $table) {
            // 1. id bigint(20) UNSIGNED AUTO_INCREMENT
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            // 2. payslip_id bigint(20) UNSIGNED NULL
            $table->foreignId('payslip_id')
                  ->nullable()
                  ->constrained('payslips')
                  ->onDelete('cascade');

            // 3. item_id int(11) No None
            // This is usually a reference to an Allowance or Deduction ID
            $table->integer('item_id');

            // 4. type varchar(255) No None
            // Used to distinguish between 'allowance' or 'deduction'
            $table->string('type');

            // 5 & 6. created_at and updated_at timestamps NULL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('employee_allowances');
        Schema::dropIfExists('employee_salary_details');
    }
};

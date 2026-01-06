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
        if (Schema::hasTable('employee_details')) {
            Schema::table('employee_details', function (Blueprint $table) {
                // Renaming columns to match Model
                if (Schema::hasColumn('employee_details', 'employee_id') && !Schema::hasColumn('employee_details', 'emp_id')) {
                    $table->renameColumn('employee_id', 'emp_id');
                }
                if (Schema::hasColumn('employee_details', 'joining_date') && !Schema::hasColumn('employee_details', 'date_joined')) {
                    $table->renameColumn('joining_date', 'date_joined');
                }
                if (Schema::hasColumn('employee_details', 'date_of_birth') && !Schema::hasColumn('employee_details', 'dob')) {
                    $table->renameColumn('date_of_birth', 'dob');
                }

                // Adding missing columns
                if (!Schema::hasColumn('employee_details', 'passport_no')) $table->string('passport_no')->nullable();
                if (!Schema::hasColumn('employee_details', 'passport_expiry_date')) $table->date('passport_expiry_date')->nullable();
                if (!Schema::hasColumn('employee_details', 'passport_tel')) $table->string('passport_tel')->nullable();
                if (!Schema::hasColumn('employee_details', 'nationality')) $table->string('nationality')->nullable();
                if (!Schema::hasColumn('employee_details', 'religion')) $table->string('religion')->nullable();
                if (!Schema::hasColumn('employee_details', 'ethnicity')) $table->string('ethnicity')->nullable();
                if (!Schema::hasColumn('employee_details', 'spouse_occupation')) $table->string('spouse_occupation')->nullable();
                if (!Schema::hasColumn('employee_details', 'no_of_children')) $table->integer('no_of_children')->default(0);
                if (!Schema::hasColumn('employee_details', 'emergency_contacts')) $table->json('emergency_contacts')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_details')) {
            Schema::table('employee_details', function (Blueprint $table) {
                 // Reverting renames
                 if (Schema::hasColumn('employee_details', 'emp_id')) {
                    $table->renameColumn('emp_id', 'employee_id');
                }
                if (Schema::hasColumn('employee_details', 'date_joined')) {
                    $table->renameColumn('date_joined', 'joining_date');
                }
                if (Schema::hasColumn('employee_details', 'dob')) {
                    $table->renameColumn('dob', 'date_of_birth');
                }

                $table->dropColumn([
                    'passport_no', 'passport_expiry_date', 'passport_tel', 'nationality', 
                    'religion', 'ethnicity', 'spouse_occupation', 'no_of_children', 'emergency_contacts'
                ]);
            });
        }
    }
};

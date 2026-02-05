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
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
            $table->string('passport_no')->nullable();
            $table->string('passport_expiry_date')->nullable();
            $table->string('passport_tel')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('spouse_occupation')->nullable();
            $table->string('no_of_children')->nullable();
            $table->longText('emergency_contacts')->nullable();
            $table->date('date_joined')->nullable();
            $table->date('dob')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('job_position_id')->nullable()->constrained('job_positions')->onDelete('set null');
            $table->string('job_title')->nullable();
            $table->timestamps();
        });

        Schema::create('user_family_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('picture')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('employee_detail_id')->constrained('employee_details')->onDelete('cascade');
            $table->string('company');
            $table->string('location')->nullable();
            $table->string('position')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_education', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('employee_detail_id')->nullable()->constrained('employee_details')->onDelete('cascade');
            $table->string('institution')->nullable();
            $table->string('subject')->nullable();
            $table->string('course')->nullable();
            $table->string('grade')->nullable();
            $table->string('file')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_positions');
        Schema::dropIfExists('employee_education');
        Schema::dropIfExists('employee_work_experiences');
        Schema::dropIfExists('user_family_infos');
        Schema::dropIfExists('employee_details');
    }
};

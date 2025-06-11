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
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->string('staff_name');
            $table->string('department');
            $table->date('employment_date');
            $table->string('job_title');
            $table->string('promotion');
            $table->string('transfer');
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');


            // and the rating values min 1 and max 5
            $table->integer('knowledge_of_job')->default(1);
            $table->integer('quality_of_work')->default(1);
            $table->integer('quantity_of_work')->default(1);
            $table->integer('emotional_intelligence')->default(1);
            $table->integer('time_management')->default(1);
            $table->integer('initiative_and_creativity')->default(1);
            $table->integer('team_work')->default(1);
            $table->integer('accountablity')->default(1);
            $table->integer('attendance_and_punctuality')->default(1);
            $table->integer('company_resource_usage_and_protection')->default(1);
            $table->integer('communication_skills')->default(1);
            $table->timestamps();

            // calc results
            $table->decimal('average_score', 5, 2);
            $table->string('performance_result');
            $table->string('rating_remark')->nullable();


            // Signatures
            $table->timestamp('employee_signed_at')->nullable();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->string('evaluator_name')->nullable();
            $table->timestamp('evaluator_signed_at')->nullable();
            $table->string('employee_name_signed')->nullable();
            $table->timestamp('employee_signed_date')->nullable();
            $table->string('department_manager')->nullable();
            $table->timestamp('department_manager_signed_at')->nullable();
            $table->timestamp('hr_received_date')->nullable();

            // superior feedback
            $table->text('needs_improvement')->nullable();
            $table->text('improvement_action')->nullable();
            $table->text('unmet_expectations')->nullable();
            $table->text('faced_problems')->nullable();
            $table->text('problem_solution')->nullable();
            $table->text('other_comment')->nullable();


            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
};

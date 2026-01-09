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
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('review_period');
            $table->date('review_date');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->integer('technical_skills')->nullable();
            $table->integer('communication')->nullable();
            $table->integer('teamwork')->nullable();
            $table->integer('punctuality')->nullable();
            $table->integer('initiative')->nullable();
            $table->integer('quality_of_work')->nullable();
            $table->integer('productivity')->nullable();
            $table->integer('leadership')->nullable();
            $table->integer('problem_solving')->nullable();
            $table->integer('adaptability')->nullable();
            $table->decimal('percentage_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals')->nullable();
            $table->text('comments')->nullable();
            $table->string('overall_rating')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('reviewer_id');
            $table->index('review_date');
        });

        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('award_name');
            $table->string('award_type')->nullable();
            $table->date('award_date');
            $table->string('gift')->nullable();
            $table->decimal('cash_price', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('employee_evaluator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->string('relationship_type')->default('supervisor');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('evaluator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_evaluator');
        Schema::dropIfExists('awards');
        Schema::dropIfExists('performances');
    }
};

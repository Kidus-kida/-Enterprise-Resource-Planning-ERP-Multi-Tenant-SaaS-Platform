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
        Schema::create('leave_accrual_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('accrued_gain_time', ['start', 'end'])->default('start');
            $table->enum('carry_over_time', ['year_start', 'allocation', 'other'])->default('year_start');
            $table->integer('carry_over_day')->nullable();
            $table->integer('carry_over_month')->nullable();
            $table->boolean('is_based_on_worked_time')->default(false);
            $table->enum('transition_mode', ['immediately', 'after_accrual'])->default('after_accrual');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('leave_accrual_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_accrual_plan_id')->constrained('leave_accrual_plans')->onDelete('cascade');
            $table->integer('sequence')->default(0);

            $table->integer('start_count')->default(0);
            $table->enum('start_type', ['days', 'months', 'years'])->default('days');

            $table->decimal('accrual_amount', 10, 4);
            $table->enum('accrual_unit', ['days', 'hours'])->default('days');


            $table->enum('accrual_frequency', [
                'hourly',
                'daily',
                'weekly',
                'biweekly',
                'monthly',
                'biyearly',
                'yearly'
            ])->default('monthly');
            $table->decimal('yearly_cap', 10, 4)->nullable();
            $table->enum('yearly_cap_unit', ['days', 'hours'])->default('days');
            $table->decimal('cap_accrued_time', 10, 4)->nullable();
            $table->enum('balance_cap_unit', ['days', 'hours'])->default('days');
            $table->enum('action_with_unused_accruals', ['lost', 'all', 'maximum'])->default('all');
            $table->decimal('max_carryover', 10, 4)->nullable();
            $table->enum('max_carryover_unit', ['days', 'hours'])->default('days');

            $table->integer('carryover_validity_period')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->foreign('default_accrual_plan_id')->references('id')->on('leave_accrual_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropForeign(['default_accrual_plan_id']);
        });
        Schema::dropIfExists('leave_accrual_levels');
        Schema::dropIfExists('leave_accrual_plans');
    }
};

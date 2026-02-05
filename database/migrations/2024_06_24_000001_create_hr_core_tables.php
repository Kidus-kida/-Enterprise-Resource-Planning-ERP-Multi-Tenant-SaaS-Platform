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
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('color')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('startDate');
            $table->date('endDate');
            $table->integer('total_days');
            $table->text('description')->nullable();
            $table->enum('duration', ['full_day', 'half_day'])->default('full_day');
            $table->json('applicable_to')->nullable();
            $table->boolean('exclude_from_leave')->default(true);
            $table->enum('weekend_adjustment', ['none', 'next_monday', 'previous_friday'])
                ->default('none');
            $table->boolean('is_paid')->default(true);
            $table->boolean('block_leave_requests')->default(false);
            $table->boolean('allow_attendance_exception')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('designations');
    }
};

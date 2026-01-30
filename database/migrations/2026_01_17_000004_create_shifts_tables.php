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
        // Create shifts table
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Morning Shift", "Night Shift"
            $table->string('code')->unique(); // e.g., "MORNING", "NIGHT"
            $table->time('start_time'); // e.g., 08:00:00
            $table->time('end_time'); // e.g., 17:00:00
            $table->integer('grace_period_minutes')->default(15);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('work_days')->nullable(); // JSON array: [1,2,3,4,5] for Mon-Fri
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Create user_shifts table (optional assignment)
        Schema::create('user_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id');
            $table->date('effective_from'); // When this shift assignment starts
            $table->date('effective_until')->nullable(); // When it ends (null = indefinite)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'effective_from']);
            $table->index(['shift_id', 'is_active']);
            $table->index('user_id');
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shifts');
        Schema::dropIfExists('shifts');
    }
};

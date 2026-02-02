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
        Schema::table('holidays', function (Blueprint $table) {
            // Duration type (full_day, half_day)
            $table->enum('duration', ['full_day', 'half_day'])->default('full_day')->after('endDate');
            
            // Applicability - JSON field to store which employees/departments/locations this applies to
            // Format: {"type": "all|departments|locations|employees", "ids": [1,2,3]}
            $table->json('applicable_to')->nullable()->after('duration');
            
            // Exclude from leave calculations (most holidays should be excluded)
            $table->boolean('exclude_from_leave')->default(true)->after('applicable_to');
            
            // Weekend adjustment rule
            $table->enum('weekend_adjustment', ['none', 'next_monday', 'previous_friday'])
                  ->default('none')
                  ->after('exclude_from_leave');
            
            // Is this a paid holiday?
            $table->boolean('is_paid')->default(true)->after('weekend_adjustment');
            
            // Block leave requests on this day
            $table->boolean('block_leave_requests')->default(false)->after('is_paid');
            
            // Allow attendance exception for critical staff
            $table->boolean('allow_attendance_exception')->default(false)->after('block_leave_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn([
                'duration',
                'applicable_to',
                'exclude_from_leave',
                'weekend_adjustment',
                'is_paid',
                'block_leave_requests',
                'allow_attendance_exception'
            ]);
        });
    }
};

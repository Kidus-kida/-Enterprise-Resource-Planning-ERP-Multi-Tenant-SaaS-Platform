<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename the setting from missing_clockout_penalty_enabled to missing_punch_enabled
        // and update category from 'penalties' to 'policies'
        DB::table('attendance_settings')
            ->where('key', 'missing_clockout_penalty_enabled')
            ->update([
                'key' => 'missing_punch_enabled',
                'category' => 'policies',
                'label' => 'Missing Clock-In/Out Enabled',
                'description' => 'Handle incomplete attendance records',
                'display_order' => 8,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the old setting name and category
        DB::table('attendance_settings')
            ->where('key', 'missing_punch_enabled')
            ->update([
                'key' => 'missing_clockout_penalty_enabled',
                'category' => 'penalties',
                'label' => 'Missing Clock-Out Penalty',
                'description' => 'Apply penalty for missing clock-out',
                'display_order' => 3,
            ]);
    }
};

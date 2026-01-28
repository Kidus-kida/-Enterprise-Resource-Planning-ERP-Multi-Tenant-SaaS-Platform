<?php

namespace App\Jobs;

use App\Models\AttendanceTimestamp;
use App\Models\AttendanceSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AutoClockoutUnsignedAttendances implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 
     * Automatically clocks out employees who forgot to clock out,
     * based on configurable settings.
     */
    public function handle(): void
    {
        // Check if auto clock-out is enabled
        if (!attendance_setting('auto_clockout_enabled', true)) {
            \Log::info('Auto clock-out is disabled in settings');
            return;
        }

        $autoClockoutTime = attendance_setting('auto_clockout_time', '23:59');
        
        // Find all unsigned timestamps from previous days
        $timestamps = AttendanceTimestamp::whereNotNull('attendance_id')
            ->whereNull('endTime')
            ->whereDate('startTime', '<', today())
            ->get();

        if ($timestamps->isEmpty()) {
            \Log::info('No unsigned attendances found for auto clock-out');
            return;
        }

        $clockedOutCount = 0;

        foreach ($timestamps as $timestamp) {
            // Set endTime to the configured auto clock-out time on the same day as startTime
            $clockOutDateTime = Carbon::parse($timestamp->startTime->format('Y-m-d') . ' ' . $autoClockoutTime);
            
            $timestamp->update([
                'endTime' => $clockOutDateTime,
            ]);
            
            $timestamp->attendance->update([
                'endDate' => $clockOutDateTime,
            ]);

            $clockedOutCount++;
        }

        \Log::info("Auto clock-out completed: {$clockedOutCount} employees clocked out at {$autoClockoutTime}");
    }
}

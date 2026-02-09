<?php

if (!function_exists('attendance_setting')) {
    /**
     * Get an attendance setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function attendance_setting(string $key, $default = null)
    {
        return \App\Models\AttendanceSetting::get($key, $default);
    }
}

if (!function_exists('is_late_arrival')) {
    /**
     * Check if clock-in time is late
     * 
     * @param string|\Carbon\Carbon $clockInTime
     * @return bool
     */
    function is_late_arrival($clockInTime): bool
    {
        $clockIn = \Carbon\Carbon::parse($clockInTime);
        $workStart = \Carbon\Carbon::parse(attendance_setting('work_day_start_time', '08:00'));
        $gracePeriod = attendance_setting('grace_period_minutes', 15);
        
        $allowedTime = $workStart->copy()->addMinutes($gracePeriod);
        
        return $clockIn->greaterThan($allowedTime);
    }
}

if (!function_exists('is_early_departure')) {
    /**
     * Check if clock-out time is early
     * 
     * @param string|\Carbon\Carbon $clockOutTime
     * @return bool
     */
    function is_early_departure($clockOutTime): bool
    {
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        $workEnd = \Carbon\Carbon::parse(attendance_setting('work_day_end_time', '17:00'));
        
        return $clockOut->lessThan($workEnd);
    }
}













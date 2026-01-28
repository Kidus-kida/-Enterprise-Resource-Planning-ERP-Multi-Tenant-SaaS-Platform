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

if (!function_exists('calculate_late_penalty')) {
    /**
     * Calculate penalty for late arrival
     * 
     * @return float
     */
    function calculate_late_penalty(): float
    {
        if (!attendance_setting('late_arrival_penalty_enabled', false)) {
            return 0;
        }
        
        return attendance_setting('late_arrival_penalty_amount', 0);
    }
}

if (!function_exists('calculate_early_departure_penalty')) {
    /**
     * Calculate penalty for early departure
     * 
     * @return float
     */
    function calculate_early_departure_penalty(): float
    {
        if (!attendance_setting('early_departure_penalty_enabled', false)) {
            return 0;
        }
        
        return attendance_setting('early_departure_penalty_amount', 0);
    }
}

if (!function_exists('is_gps_required')) {
    /**
     * Check if GPS is required for clock-in
     * 
     * @return bool
     */
    function is_gps_required(): bool
    {
        return attendance_setting('require_gps', false);
    }
}

if (!function_exists('is_geofencing_enabled')) {
    /**
     * Check if geofencing is enabled
     * 
     * @return bool
     */
    function is_geofencing_enabled(): bool
    {
        return attendance_setting('enable_geofencing', false);
    }
}

if (!function_exists('is_within_allowed_location')) {
    /**
     * Check if coordinates are within allowed locations
     * 
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    function is_within_allowed_location(float $latitude, float $longitude): bool
    {
        if (!is_geofencing_enabled()) {
            return true; // Geofencing disabled, allow all locations
        }
        
        $allowedLocations = attendance_setting('allowed_locations', []);
        $radius = attendance_setting('location_radius_meters', 100);
        
        if (empty($allowedLocations)) {
            return true; // No locations configured, allow all
        }
        
        foreach ($allowedLocations as $location) {
            $distance = haversine_distance(
                $latitude,
                $longitude,
                $location['latitude'],
                $location['longitude']
            );
            
            if ($distance <= $radius) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('haversine_distance')) {
    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    function haversine_distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

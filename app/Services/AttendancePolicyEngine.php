<?php

namespace App\Services;

use App\Models\AttendanceTimestamp;
use App\Models\AttendancePolicyEvent;
use App\Models\AttendanceSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendancePolicyEngine
{
    /**
     * Evaluate a clock-in event
     * 
     * @param AttendanceTimestamp $timestamp
     * @return array Policy evaluation results
     */
    public function evaluateClockIn(AttendanceTimestamp $timestamp): array
    {
        $violations = [];

        // Evaluate late arrival
        $lateArrival = $this->evaluateLateArrival($timestamp);
        if ($lateArrival) {
            $violations[] = $lateArrival;
        }

        // Evaluate location compliance
        $locationViolation = $this->evaluateLocationCompliance($timestamp, 'clock_in');
        if ($locationViolation) {
            $violations[] = $locationViolation;
        }

        // Store all violations
        foreach ($violations as $violation) {
            $this->recordPolicyEvent($violation);
        }

        return $violations;
    }

    /**
     * Evaluate a clock-out event
     * 
     * @param AttendanceTimestamp $timestamp
     * @return array Policy evaluation results
     */
    public function evaluateClockOut(AttendanceTimestamp $timestamp): array
    {
        $violations = [];

        // Evaluate early departure
        $earlyDeparture = $this->evaluateEarlyDeparture($timestamp);
        if ($earlyDeparture) {
            $violations[] = $earlyDeparture;
        }

        // Evaluate minimum work hours
        $insufficientHours = $this->evaluateMinimumWorkHours($timestamp);
        if ($insufficientHours) {
            $violations[] = $insufficientHours;
        }

        // Evaluate location compliance
        $locationViolation = $this->evaluateLocationCompliance($timestamp, 'clock_out');
        if ($locationViolation) {
            $violations[] = $locationViolation;
        }

        // Store all violations
        foreach ($violations as $violation) {
            $this->recordPolicyEvent($violation);
        }

        return $violations;
    }

    /**
     * Evaluate late arrival policy
     * 
     * OPTIONAL SHIFT SUPPORT:
     * - If user has shift assigned: Use shift times
     * - If no shift assigned: Use global settings (flexible)
     * - Does NOT affect payroll unless explicitly enabled
     * 
     * @param AttendanceTimestamp $timestamp
     * @return array|null Violation data or null
     */
    protected function evaluateLateArrival(AttendanceTimestamp $timestamp): ?array
    {
        // Check if shift enforcement is enabled
        $useShifts = attendance_setting('shifts_enabled', false);
        
        $workStartTime = null;
        $gracePeriodMinutes = attendance_setting('grace_in_minutes', 15);
        $shiftUsed = false;
        
        // Try to get shift for this user (OPTIONAL)
        if ($useShifts) {
            $shift = \App\Models\UserShift::getShiftForUser(
                $timestamp->user_id,
                $timestamp->startTime->format('Y-m-d')
            );
            
            if ($shift) {
                $workStartTime = $shift->start_time->format('H:i');
                $gracePeriodMinutes = $shift->grace_period_minutes;
                $shiftUsed = true;
            }
        }
        
        // Fallback to global settings if no shift (FLEXIBLE DEFAULT)
        if (!$workStartTime) {
            $workStartTime = attendance_setting('work_day_start_time', '08:00');
        }

        $penaltyEnabled = attendance_setting('late_arrival_penalty_enabled', false);
        $penaltyAmount = attendance_setting('late_arrival_penalty_amount', 0);

        // Calculate allowed start time (work start + grace period)
        $workStart = Carbon::parse($timestamp->startTime->format('Y-m-d') . ' ' . $workStartTime);
        $allowedTime = $workStart->copy()->addMinutes($gracePeriodMinutes);
        $clockInTime = Carbon::parse($timestamp->startTime);

        // Check if late
        if ($clockInTime->lessThanOrEqualTo($allowedTime)) {
            return null; // Not late
        }

        $minutesLate = $clockInTime->diffInMinutes($workStart);

        return [
            'attendance_timestamp_id' => $timestamp->id,
            'user_id' => $timestamp->user_id,
            'event_type' => 'clock_in',
            'policy_type' => 'late_arrival',
            'is_violation' => true,
            'penalty_amount' => $penaltyEnabled ? $penaltyAmount : 0,
            'message' => sprintf(
                'Arrived %d minutes late (clocked in at %s, expected by %s)%s',
                $minutesLate,
                $clockInTime->format('H:i'),
                $allowedTime->format('H:i'),
                $shiftUsed ? ' [Shift-based]' : ''
            ),
            'metadata' => [
                'clock_in_time' => $clockInTime->format('H:i:s'),
                'expected_time' => $workStart->format('H:i:s'),
                'grace_period_end' => $allowedTime->format('H:i:s'),
                'minutes_late' => $minutesLate,
                'penalty_enabled' => $penaltyEnabled,
                'shift_used' => $shiftUsed,
            ],
        ];
    }

    /**
     * Evaluate early departure policy
     * 
     * OPTIONAL SHIFT SUPPORT:
     * - If user has shift assigned: Use shift end time
     * - If no shift assigned: Use global settings (flexible)
     * 
     * @param AttendanceTimestamp $timestamp
     * @return array|null Violation data or null
     */
    protected function evaluateEarlyDeparture(AttendanceTimestamp $timestamp): ?array
    {
        if (!$timestamp->endTime) {
            return null; // Not clocked out yet
        }

        // Check if shift enforcement is enabled
        $useShifts = attendance_setting('shifts_enabled', false);
        
        $workEndTime = null;
        $graceOutMinutes = attendance_setting('grace_out_minutes', 10);
        $shiftUsed = false;
        $isNightShift = false;
        
        // Try to get shift for this user (OPTIONAL)
        if ($useShifts) {
            $shift = \App\Models\UserShift::getShiftForUser(
                $timestamp->user_id,
                $timestamp->endTime->format('Y-m-d')
            );
            
            if ($shift) {
                $workEndTime = $shift->end_time->format('H:i');
                // Use shift-specific grace_out if available, otherwise global
                $graceOutMinutes = $shift->grace_out_minutes ?? $graceOutMinutes;
                $isNightShift = $shift->isNightShift();
                $shiftUsed = true;
            }
        }
        
        // Fallback to global settings if no shift (FLEXIBLE DEFAULT)
        if (!$workEndTime) {
            $workEndTime = attendance_setting('work_day_end_time', '17:00');
        }

        $penaltyEnabled = attendance_setting('early_departure_penalty_enabled', false);
        $penaltyAmount = attendance_setting('early_departure_penalty_amount', 0);

        // Handle night shifts: if shift crosses midnight, clock-out could be next day
        $workEndDate = $timestamp->endTime->format('Y-m-d');
        if ($isNightShift) {
            // For night shifts, check if clock-out is in early morning (likely part of shift)
            $clockOutHour = (int) $timestamp->endTime->format('H');
            $endHour = (int) substr($workEndTime, 0, 2);
            
            // If clock-out is in early morning (00-12) and shift end is also early, same day
            // Otherwise if clock-out is late night, might need to compare to next day's end
            if ($clockOutHour >= 12 && $endHour < 12) {
                $workEndDate = $timestamp->endTime->copy()->addDay()->format('Y-m-d');
            }
        }

        $workEnd = Carbon::parse($workEndDate . ' ' . $workEndTime);
        
        // Apply grace period: can leave up to X minutes before scheduled end
        $allowedEarliestTime = $workEnd->copy()->subMinutes($graceOutMinutes);
        $clockOutTime = Carbon::parse($timestamp->endTime);

        // Check if left early (before allowed earliest time)
        if ($clockOutTime->greaterThanOrEqualTo($allowedEarliestTime)) {
            return null; // Not early (within grace period or after end time)
        }

        $minutesEarly = $allowedEarliestTime->diffInMinutes($clockOutTime);

        return [
            'attendance_timestamp_id' => $timestamp->id,
            'user_id' => $timestamp->user_id,
            'event_type' => 'clock_out',
            'policy_type' => 'early_departure',
            'is_violation' => true,
            'penalty_amount' => $penaltyEnabled ? $penaltyAmount : 0,
            'message' => sprintf(
                'Left %d minutes early (clocked out at %s, expected after %s with %d min grace)%s%s',
                $minutesEarly,
                $clockOutTime->format('H:i'),
                $workEnd->format('H:i'),
                $graceOutMinutes,
                $shiftUsed ? ' [Shift-based]' : '',
                $isNightShift ? ' [Night Shift]' : ''
            ),
            'metadata' => [
                'clock_out_time' => $clockOutTime->format('H:i:s'),
                'expected_time' => $workEnd->format('H:i:s'),
                'grace_period_minutes' => $graceOutMinutes,
                'earliest_allowed_time' => $allowedEarliestTime->format('H:i:s'),
                'minutes_early' => $minutesEarly,
                'penalty_enabled' => $penaltyEnabled,
                'shift_used' => $shiftUsed,
                'is_night_shift' => $isNightShift,
            ],
        ];
    }

    /**
     * Evaluate minimum work hours policy
     * 
     * @param AttendanceTimestamp $timestamp
     * @return array|null Violation data or null
     */
    protected function evaluateMinimumWorkHours(AttendanceTimestamp $timestamp): ?array
    {
        if (!$timestamp->endTime) {
            return null; // Not clocked out yet
        }

        $minimumHours = attendance_setting('minimum_work_hours', 8);
        
        $actualHours = Carbon::parse($timestamp->endTime)
            ->diffInHours(Carbon::parse($timestamp->startTime), true);

        // Check if worked enough hours
        if ($actualHours >= $minimumHours) {
            return null; // Meets minimum
        }

        $shortfall = $minimumHours - $actualHours;

        return [
            'attendance_timestamp_id' => $timestamp->id,
            'user_id' => $timestamp->user_id,
            'event_type' => 'clock_out',
            'policy_type' => 'insufficient_hours',
            'is_violation' => true,
            'penalty_amount' => 0, // No direct penalty, but flagged
            'message' => sprintf(
                'Worked %.2f hours, %.2f hours short of minimum %d hours',
                $actualHours,
                $shortfall,
                $minimumHours
            ),
            'metadata' => [
                'actual_hours' => round($actualHours, 2),
                'minimum_hours' => $minimumHours,
                'shortfall_hours' => round($shortfall, 2),
            ],
        ];
    }

    /**
     * Evaluate location compliance
     * 
     * @param AttendanceTimestamp $timestamp
     * @param string $eventType
     * @return array|null Violation data or null
     */
    protected function evaluateLocationCompliance(AttendanceTimestamp $timestamp, string $eventType): ?array
    {
        $requireGPS = attendance_setting('require_gps', false);
        $geofencingEnabled = attendance_setting('enable_geofencing', false);

        $location = $eventType === 'clock_in' ? $timestamp->location : $timestamp->co_location;

        // Check if GPS is required but not provided
        if ($requireGPS && empty($location)) {
            return [
                'attendance_timestamp_id' => $timestamp->id,
                'user_id' => $timestamp->user_id,
                'event_type' => $eventType,
                'policy_type' => 'missing_gps',
                'is_violation' => true,
                'penalty_amount' => 0,
                'message' => 'GPS location not provided',
                'metadata' => [
                    'require_gps' => true,
                    'location_provided' => false,
                ],
            ];
        }

        // Check geofencing if enabled
        if ($geofencingEnabled && !empty($location)) {
            // Note: This requires GPS coordinates to be stored separately
            // For now, we'll just flag that geofencing should be checked
            // In a real implementation, you'd parse coordinates from metadata
            return [
                'attendance_timestamp_id' => $timestamp->id,
                'user_id' => $timestamp->user_id,
                'event_type' => $eventType,
                'policy_type' => 'geofence_check_required',
                'is_violation' => false,
                'penalty_amount' => 0,
                'message' => 'Geofencing check required (implement coordinate validation)',
                'metadata' => [
                    'geofencing_enabled' => true,
                    'location' => $location,
                ],
            ];
        }

        return null; // No location violation
    }

    /**
     * Record a policy event to the database
     * 
     * @param array $eventData
     * @return AttendancePolicyEvent
     */
    protected function recordPolicyEvent(array $eventData): AttendancePolicyEvent
    {
        return AttendancePolicyEvent::create(array_merge($eventData, [
            'status' => 'pending',
            'evaluated_at' => now(),
        ]));
    }

    /**
     * Get all policy events for a timestamp
     * 
     * @param int $timestampId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPolicyEvents(int $timestampId)
    {
        return AttendancePolicyEvent::where('attendance_timestamp_id', $timestampId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get total penalties for a user in a date range
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalPenalties(int $userId, string $startDate, string $endDate): float
    {
        return AttendancePolicyEvent::forUser($userId)
            ->violations()
            ->withStatus('applied')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('penalty_amount');
    }

    /**
     * Get violation summary for a user
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getViolationSummary(int $userId, string $startDate, string $endDate): array
    {
        $violations = AttendancePolicyEvent::forUser($userId)
            ->violations()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_violations' => $violations->count(),
            'late_arrivals' => $violations->where('policy_type', 'late_arrival')->count(),
            'early_departures' => $violations->where('policy_type', 'early_departure')->count(),
            'insufficient_hours' => $violations->where('policy_type', 'insufficient_hours')->count(),
            'location_violations' => $violations->whereIn('policy_type', ['missing_gps', 'geofence_violation'])->count(),
            'total_penalties' => $violations->where('status', 'applied')->sum('penalty_amount'),
            'pending_penalties' => $violations->where('status', 'pending')->sum('penalty_amount'),
        ];
    }

    /**
     * Apply all pending penalties for a user
     * 
     * @param int $userId
     * @return int Number of penalties applied
     */
    public function applyPendingPenalties(int $userId): int
    {
        $count = 0;

        DB::transaction(function () use ($userId, &$count) {
            $pending = AttendancePolicyEvent::forUser($userId)
                ->violations()
                ->withStatus('pending')
                ->where('penalty_amount', '>', 0)
                ->get();

            foreach ($pending as $event) {
                $event->markAsApplied();
                $count++;
            }
        });

        return $count;
    }
}

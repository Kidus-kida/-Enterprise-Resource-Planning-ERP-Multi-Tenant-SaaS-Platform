<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'grace_out_minutes',
        'is_active',
        'deactivated_by_system',
        'description',
        'work_days',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'grace_period_minutes' => 'integer',
        'grace_out_minutes' => 'integer',
        'is_active' => 'boolean',
        'deactivated_by_system' => 'boolean',
        'work_days' => 'array', // JSON array of day numbers (1=Monday, 7=Sunday)
    ];

    /**
     * Get user assignments for this shift
     */
    public function userShifts(): HasMany
    {
        return $this->hasMany(UserShift::class);
    }

    /**
     * Scope: Only active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given day is a work day for this shift
     * 
     * @param int $dayOfWeek Day number (1=Monday, 7=Sunday, 0=Sunday)
     * @return bool
     */
    public function isWorkDay($dayOfWeek): bool
    {
        // If no work days are set, assume all days are work days
        if (empty($this->work_days)) {
            return true;
        }

        // Convert Sunday from 0 to 7 for consistency
        if ($dayOfWeek === 0) {
            $dayOfWeek = 7;
        }

        // Ensure work_days is an array (in case the cast fails)
        $workDays = $this->work_days;
        if (is_string($workDays)) {
            $workDays = json_decode($workDays, true) ?? [];
        }
        
        if (!is_array($workDays)) {
            $workDays = [];
        }

        return in_array($dayOfWeek, $workDays);
    }

    /**
     * Get the formatted start time
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time->format('H:i');
    }

    /**
     * Get the formatted end time
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time->format('H:i');
    }

    /**
     * Get shift duration in hours
     */
    public function getDurationHoursAttribute(): float
    {
        return $this->end_time->diffInHours($this->start_time);
    }

    /**
     * Check if this is a night shift (crosses midnight)
     * 
     * @return bool
     */
    public function isNightShift(): bool
    {
        return $this->end_time < $this->start_time;
    }

    /**
     * Check if this shift overlaps with the restricted night time range
     * or is a cross-midnight shift.
     * 
     * @return bool
     */
    public function isRestrictedNightShift(): bool
    {
        // Case 1: Crosses midnight
        if ($this->isNightShift()) {
            return true;
        }

        $nightStart = AttendanceSetting::get('night_time_start', '22:00');
        $nightEnd = AttendanceSetting::get('night_time_end', '06:00');
        $startTime = $this->start_time->format('H:i');
        $endTime = $this->end_time->format('H:i');

        // Case 2: Restricted range crosses midnight (e.g. 22:00 - 06:00)
        if ($nightEnd < $nightStart) {
            $overlapsSegment1 = ($startTime < '23:59' && $endTime > $nightStart);
            $overlapsSegment2 = ($startTime < $nightEnd && $endTime > '00:00');
            return $overlapsSegment1 || $overlapsSegment2;
        } 
        
        // Case 3: Restricted range is within same day
        return ($startTime < $nightEnd && $endTime > $nightStart);
    }

    /**
     * Get rotation steps this shift is part of
     */
    public function rotationSteps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ShiftRotationStep::class);
    }
}

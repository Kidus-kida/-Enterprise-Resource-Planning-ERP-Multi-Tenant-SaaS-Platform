<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends TenantModel
{
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'grace_out_minutes',
        'is_active',
        'description',
        'work_days',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'grace_period_minutes' => 'integer',
        'grace_out_minutes' => 'integer',
        'is_active' => 'boolean',
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
    public function isWorkDay(int $dayOfWeek): bool
    {
        // If no work_days specified, all days are work days
        if (empty($this->work_days)) {
            return true;
        }

        // Convert Sunday from 0 to 7 for consistency
        if ($dayOfWeek === 0) {
            $dayOfWeek = 7;
        }

        return in_array($dayOfWeek, $this->work_days);
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
}


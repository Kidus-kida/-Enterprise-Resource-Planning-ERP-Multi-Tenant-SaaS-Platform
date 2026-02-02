<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserShift extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope: Active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Effective on a given date
     */
    public function scopeEffectiveOn($query, string $date)
    {
        $date = Carbon::parse($date);

        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date);
            });
    }

    /**
     * Check if this assignment is effective on a given date
     */
    public function isEffectiveOn(string $date): bool
    {
        $date = Carbon::parse($date);

        if ($date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * Resolve the shift for a specific time, handling night shift rollovers.
     */
    public static function getShiftForTime(int $userId, Carbon $time): ?Shift
    {
        // 1. If it's early morning (before 10 AM), check if YESTERDAY had a night shift
        // that is still relevant for this punch.
        if ($time->hour < 10) {
            $yesterday = $time->copy()->subDay();
            $yesterdayShift = self::getShiftForUser($userId, $yesterday->format('Y-m-d'));
            
            if ($yesterdayShift && $yesterdayShift->isNightShift()) {
                // If clocking out, we almost certainly belong to yesterday's night shift.
                // If clocking in, it's more ambiguous, but usually still yesterday's.
                return $yesterdayShift;
            }
        }

        // 2. Otherwise, return today's assigned shift
        return self::getShiftForUser($userId, $time->format('Y-m-d'));
    }

    public static function getShiftForUser(int $userId, string $date): ?Shift
    {
        $dateObj = Carbon::parse($date);

        // 0. Check for manual overrides (Highest Priority)
        $override = ShiftOverride::where('user_id', $userId)
            ->where('date', $dateObj->format('Y-m-d'))
            ->where('is_active', true)
            ->with('shift')
            ->first();

        if ($override) {
            return $override->shift; 
        }
        
        // 1. Automatic Enrollment: Check if user's fixed/base shift is part of a rotation
        $rotationalEnabled = \App\Models\AttendanceSetting::get('rotational_shift_enabled', false);
        $rotation = null;
        $baseShiftId = null;
        $baseAssignment = null;

        if ($rotationalEnabled) {
            $baseAssignment = self::where('user_id', $userId)
                ->active()
                ->effectiveOn($date)
                ->first();
            
            if ($baseAssignment) {
                $baseShiftId = $baseAssignment->shift_id;
                $rotation = ShiftRotation::where('is_active', true)
                    ->whereHas('steps', function ($query) use ($baseShiftId) {
                        $query->where('shift_id', $baseShiftId);
                    })
                    ->with(['steps.shift'])
                    ->first();
            }
        }

        // If we found a rotation plan (either via assignment or base shift)
        if ($rotation) {
            $steps = $rotation->steps;
            if ($steps->count() > 0) {
                $startDate = $rotation->start_date;
                $interval = $rotation->frequency_interval > 0 ? $rotation->frequency_interval : 1;
                
                // Calculate how many cycles have passed since the global start date
                $diff = 0;
                switch ($rotation->frequency_type) {
                    case 'daily':
                        $diff = $startDate->diffInDays($dateObj, false);
                        break;
                    case 'weekly':
                        $startWeek = $startDate->copy()->startOfWeek();
                        $currentWeek = $dateObj->copy()->startOfWeek();
                        $diff = $startWeek->diffInWeeks($currentWeek, false);
                        break;
                    case 'monthly':
                        $diff = (($dateObj->year - $startDate->year) * 12) + ($dateObj->month - $startDate->month);
                        break;
                }

                $cycleNumber = floor($diff / $interval);
                $offset = 0;
                if ($baseShiftId) {
                    $stepIndex = $steps->search(fn($s) => $s->shift_id == $baseShiftId);
                    $offset = ($stepIndex !== false) ? $stepIndex : 0;
                }

                $stepCount = $steps->count();
                $currentIndex = ($cycleNumber + $offset) % $stepCount;
                if ($currentIndex < 0) $currentIndex = ($stepCount + $currentIndex) % $stepCount;
                
                $resolvedShift = $steps[$currentIndex]->shift;
                
                // Work day validation
                if ($resolvedShift && $resolvedShift->is_active && $resolvedShift->isWorkDay($dateObj->dayOfWeek)) {
                    return $resolvedShift;
                }
                
                return null;
            }
        }

        // 3. Fallback to fixed assignment
        if (!$baseAssignment) {
            $baseAssignment = self::where('user_id', $userId)
                ->active()
                ->effectiveOn($date)
                ->with('shift')
                ->first();
        } else {
            // Load shift if not already loaded
            $baseAssignment->loadMissing('shift');
        }

        if ($baseAssignment && $baseAssignment->shift && $baseAssignment->shift->is_active && $baseAssignment->shift->isWorkDay($dateObj->dayOfWeek)) {
            return $baseAssignment->shift;
        }

        return null;
    }
}

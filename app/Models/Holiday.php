<?php

namespace App\Models;

use App\Enums\CalendarColors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Holiday extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'startDate',
        'endDate',
        'description',
        'is_annual',
        'color',
        // New Odoo-style fields
        'duration',
        'applicable_to',
        'exclude_from_leave',
        'weekend_adjustment',
        'is_paid',
        'block_leave_requests',
        'allow_attendance_exception'
    ];
    
    protected $casts = [
        'color' => CalendarColors::class,
        'startDate' => 'date',
        'endDate' => 'date',
        'is_annual' => 'boolean',
        'applicable_to' => 'array',
        'exclude_from_leave' => 'boolean',
        'is_paid' => 'boolean',
        'block_leave_requests' => 'boolean',
        'allow_attendance_exception' => 'boolean',
    ];

    /**
     * Check if a specific date is a holiday
     */
    public static function isHoliday($date, $userId = null)
    {
        $date = Carbon::parse($date);
        
        $query = static::where(function ($q) use ($date) {
            $q->whereDate('startDate', '<=', $date)
              ->whereDate('endDate', '>=', $date);
        });

        // If userId provided, check applicability
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('applicable_to')
                  ->orWhereJsonContains('applicable_to->type', 'all')
                  ->orWhereJsonContains('applicable_to->ids', $userId);
            });
        }

        return $query->exists();
    }

    /**
     * Get all holidays in a date range
     */
    public static function getHolidaysInRange($startDate, $endDate, $userId = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $query = static::where(function ($q) use ($start, $end) {
            $q->whereBetween('startDate', [$start, $end])
              ->orWhereBetween('endDate', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->where('startDate', '<=', $start)
                     ->where('endDate', '>=', $end);
              });
        });

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->whereNull('applicable_to')
                  ->orWhereJsonContains('applicable_to->type', 'all')
                  ->orWhereJsonContains('applicable_to->ids', $userId);
            });
        }

        return $query->get();
    }

    /**
     * Get the adjusted date if this holiday falls on weekend
     */
    public function getAdjustedDate()
    {
        $date = $this->startDate;

        if ($this->weekend_adjustment === 'none') {
            return $date;
        }

        $dayOfWeek = $date->dayOfWeek;

        // If Saturday (6) and adjustment is previous_friday
        if ($dayOfWeek === Carbon::SATURDAY && $this->weekend_adjustment === 'previous_friday') {
            return $date->copy()->subDay();
        }

        // If Sunday (0) and adjustment is next_monday
        if ($dayOfWeek === Carbon::SUNDAY && $this->weekend_adjustment === 'next_monday') {
            return $date->copy()->addDay();
        }

        return $date;
    }

    /**
     * Check if this holiday is applicable to a specific user
     */
    public function isApplicableTo($userId)
    {
        if (!$this->applicable_to) {
            return true; // No restriction means applies to all
        }

        $applicability = $this->applicable_to;

        if ($applicability['type'] === 'all') {
            return true;
        }

        if (isset($applicability['ids']) && in_array($userId, $applicability['ids'])) {
            return true;
        }

        return false;
    }
}


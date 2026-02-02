<?php

namespace App\Services;

use App\Models\LeaveAllocation;
use App\Models\Holiday;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveService
{
    /**
     * Calculate working days between two dates, excluding weekends and holidays.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return int
     */
    public function calculateDuration($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($startDate->gt($endDate)) {
            return 0;
        }

        // Fetch overlapping holidays that should be excluded from leave
        $holidays = Holiday::where('exclude_from_leave', true)
            ->where(function($q) use ($startDate, $endDate) {
                 $q->whereBetween('startDate', [$startDate, $endDate])
                   ->orWhereBetween('endDate', [$startDate, $endDate])
                   ->orWhere(function($q2) use ($startDate, $endDate) {
                       $q2->where('startDate', '<=', $startDate)
                          ->where('endDate', '>=', $endDate);
                   });
            })->get();

        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->startDate);
            $hEnd = Carbon::parse($holiday->endDate ?? $holiday->startDate);
            
            // Constrain holiday period to the requested window for efficiency
            if ($hStart->lt($startDate)) $hStart = $startDate->copy();
            if ($hEnd->gt($endDate)) $hEnd = $endDate->copy();
            
            if ($hStart->gt($hEnd)) continue; // Should not happen with above logic but safety check

            $hPeriod = CarbonPeriod::create($hStart, $hEnd);
            foreach ($hPeriod as $date) {
                $holidayDates[] = $date->format('Y-m-d');
            }
        }
        $holidayDates = array_unique($holidayDates);

        $days = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Exclude Weekends (Sat=6, Sun=0)
            if ($date->isWeekend()) {
                continue;
            }

            // Exclude Public Holidays
            if (in_array($date->format('Y-m-d'), $holidayDates)) {
                continue;
            }

            $days++;
        }

        return $days;
    }

    /**
     * Check if user has sufficient balance for the leave request.
     *
     * @param int $userId
     * @param int $leaveTypeId
     * @param float $daysRequested
     * @return bool
     */
    public function checkBalance($userId, $leaveTypeId, $daysRequested)
    {
        $totalAvailable = LeaveAllocation::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->sum('available_days');

        // Future: Add negative balance check here based on LeaveType settings
        
        return $totalAvailable >= $daysRequested;
    }

    /**
     * Deduct days from user's allocations (FIFO - First In First Out).
     *
     * @param int $userId
     * @param int $leaveTypeId
     * @param float $daysToDeduct
     * @return void
     */
    public function deductBalance($userId, $leaveTypeId, $daysToDeduct)
    {
        // Fetch allocations with available balance, ordered by year/date (oldest first)
        $allocations = LeaveAllocation::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('available_days', '>', 0)
            ->orderBy('year', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $remaining = $daysToDeduct;

        foreach ($allocations as $allocation) {
            if ($remaining <= 0) break;

            if ($allocation->available_days >= $remaining) {
                // Allocation covers the remaining amount
                $allocation->used_days += $remaining;
                $allocation->available_days -= $remaining;
                $allocation->save();
                $remaining = 0;
            } else {
                // Consume this allocation entirely
                $available = $allocation->available_days;
                $allocation->used_days += $available;
                $allocation->available_days = 0;
                $allocation->save();
                $remaining -= $available;
            }
        }

        // If $remaining > 0 here, it means we deducted everything but still owe days.
        // This implies negative balance. logic handles it but allocations are empty.
        // We might need to record negative on the *current* year allocation if it exists, or create one.
        // For Phase 5 basic, we assume checkBalance prevented execution if insufficient.
    }
    
    /**
     * Re-credit days back to allocations (e.g. upon Rejection or Cancellation).
     * Usage: LIFO (Last In First Out) restoration usually, or just restore to latest?
     * Ideally restore to the specific allocation used. 
     * Complex without a linking table "LeaveRequestAllocation".
     * simplified: credit back to current year or most recent active allocation.
     */
     public function creditBalance($userId, $leaveTypeId, $daysToCredit)
     {
         // Find latest allocation to dump credit back into
         $allocation = LeaveAllocation::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->orderBy('year', 'desc')
            ->first();
            
         if ($allocation) {
             $allocation->used_days = max(0, $allocation->used_days - $daysToCredit);
             $allocation->available_days += $daysToCredit;
             $allocation->save();
         }
     }
}

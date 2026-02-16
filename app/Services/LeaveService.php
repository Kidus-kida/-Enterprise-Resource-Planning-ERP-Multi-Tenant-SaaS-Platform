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
            ->where('status', 'approved') // Only confirmed allocations
            ->sum('available_days');

        if ($totalAvailable >= $daysRequested) {
            return true;
        }

        // Check for Negative Balance allowance
        $leaveType = LeaveType::find($leaveTypeId);
        if ($leaveType && $leaveType->allow_negative_balance) {
            $defecit = $daysRequested - $totalAvailable;
            return $defecit <= $leaveType->max_negative_balance;
        }

        return false;
    }

    public function deductBalance($userId, $leaveTypeId, $daysToDeduct)
    {
        // Fetch allocations with available balance
        $allocations = LeaveAllocation::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'approved')
            ->where('available_days', '>', 0)
            ->orderBy('period_start', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $remaining = $daysToDeduct;

        // 1. Consume existing positive allocations
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

        // 2. Handle Defecit (Negative Balance)
        if ($remaining > 0) {
            // Find or create current year allocation to store the debt
            $currentYearAllocation = LeaveAllocation::where('user_id', $userId)
                ->where('leave_type_id', $leaveTypeId)
                ->currentYear()
                ->first();

            if (!$currentYearAllocation) {
                $currentYearAllocation = LeaveAllocation::create([
                    'user_id' => $userId,
                    'leave_type_id' => $leaveTypeId,
                    'period_start' => Carbon::now()->startOfYear(),
                    'period_end' => null,
                    'allocation_type' => 'manual', // or adjustment
                    'status' => 'approved',
                    'available_days' => 0,
                    'allocated_days' => 0,
                ]);
            }

            // Apply negative balance
            $currentYearAllocation->available_days -= $remaining; // Goes negative
            $currentYearAllocation->used_days += $remaining;
            $currentYearAllocation->save();
        }
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
            ->orderBy('period_start', 'desc')
            ->first();
            
         if ($allocation) {
             $allocation->used_days = max(0, $allocation->used_days - $daysToCredit);
             $allocation->available_days += $daysToCredit;
             $allocation->save();
         }
     }

    /**
     * Get approved leaves overlapping with a pay period.
     * Useful for Payroll calculation.
     */
    public function getApprovedLeavesForPeriod($startDate, $endDate) 
    {
        return \App\Models\LeaveRequest::with('leaveType')
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                 $q->whereBetween('leave_start_date', [$startDate, $endDate])
                   ->orWhereBetween('leave_end_date', [$startDate, $endDate])
                   ->orWhere(function($q2) use ($startDate, $endDate) {
                       $q2->where('leave_start_date', '<=', $startDate)
                          ->where('leave_end_date', '>=', $endDate);
                   });
            })
            ->get();
    }
}

<?php

namespace App\Services\Leave;

use App\Models\LeaveAllocation;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveAccrualLevel;
use App\Models\User;
use App\Models\PayrollSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AccrualService
{
    /**
     * Run daily accruals for all eligible allocations
     */
    public function runDailyAccruals()
    {
        $today = Carbon::today();

        // Find all allocations linked to an active accrual plan, approved, and current period
        $allocations = LeaveAllocation::whereNotNull('accrual_plan_id')
            ->where('status', 'approved')
            ->where(function ($query) use ($today) {
                $query->whereYear('period_start', $today->year)
                    ->orWhereYear('period_end', $today->year);
            })

            ->currentYear()
            ->get();

        foreach ($allocations as $allocation) {
            /** @var LeaveAllocation $allocation */
            $plan = $allocation->accrualPlan;
            if (!$plan || !$plan->is_active) {
                continue;
            }

            // 1. Process accrual for today
            $this->processAllocationAccrual($allocation, $plan, $today);

            // 2. Process carry-over if applicable
            $this->processCarryOverIfDue($allocation, $plan, $today);
        }
    }

    /**
     * Process accrual for a specific allocation
     */
    protected function processAllocationAccrual(LeaveAllocation $allocation, LeaveAccrualPlan $plan, Carbon $date)
    {
        $user = $allocation->user;
        $activeLevel = $this->getActiveLevel($user, $plan, $date);

        if (!$activeLevel) {
            return;
        }

        // Determine if today is an accrual date based on frequency
        if (!$this->isAccrualDate($allocation, $activeLevel, $plan, $date)) {
            return;
        }

        $accrualAmount = (float) $activeLevel->accrual_amount;

        // Handle Unit Conversion (Hours to Days)
        if ($activeLevel->accrual_unit === 'hours') {
            $workingHours = (float) PayrollSetting::get('working_hours_per_day', 8);
            $accrualAmount = $workingHours > 0 ? ($accrualAmount / $workingHours) : ($accrualAmount / 8);
        }

        // Worked Time Modifier
        if ($plan->is_based_on_worked_time) {
            $accrualAmount *= $this->getWorkedTimeModifier($user, $activeLevel, $date);
        }
        // Apply Gain Time logic (Odoo: accrue at start or end of period)
// For simplicity in this initial implementation, we accrue on the date matched by isAccrualDate.
        // Enforce Yearly Cap (from Level)
        $currentAccrued = (float) $allocation->accrued_days;
        if ($activeLevel->yearly_cap > 0 && $currentAccrued >= (float) $activeLevel->yearly_cap) {
            return;
        }

        // Calculate amount to add, respecting yearly cap
        $canAccrue = $accrualAmount;
        if ($activeLevel->yearly_cap > 0 && ($currentAccrued + $accrualAmount) > (float) $activeLevel->yearly_cap) {
            $canAccrue = (float) $activeLevel->yearly_cap - $currentAccrued;
        }

        // Update allocation balances
        $allocation->accrued_days = number_format($currentAccrued + $canAccrue, 2, '.', '');
        $allocation->available_days = number_format((float) $allocation->available_days + $canAccrue, 2, '.', '');

        // Enforce maximum cap on available_days
        if ($activeLevel->cap_accrued_time > 0 && (float) $allocation->available_days > (float) $activeLevel->cap_accrued_time) {
            $overflow = (float) $allocation->available_days - (float) $activeLevel->cap_accrued_time;
            $allocation->accrued_days = number_format((float) $allocation->accrued_days - $overflow, 2, '.', '');
            $allocation->available_days = number_format((float) $activeLevel->cap_accrued_time, 2, '.', '');
        }

        $allocation->last_accrual_date = $date->toDateString();
        $allocation->save();

        Log::info("Accrued {$canAccrue} days for User {$user->id} on Plan {$plan->name}");
    }

    /**
     * Determine the active accrual level for a user
     */
    public function getActiveLevel(User $user, LeaveAccrualPlan $plan, Carbon $date)
    {
        $joinDate = $user->employeeDetail->date_joined ?? $user->created_at;
        $tenureDays = $joinDate->diffInDays($date);
        $tenureMonths = $joinDate->diffInMonths($date);
        $tenureYears = $joinDate->diffInYears($date);

        return $plan->levels()
            ->where(function ($query) use ($tenureDays, $tenureMonths, $tenureYears) {
                $query->where(function ($q) use ($tenureDays) {
                    $q->where('start_type', 'days')->where('start_count', '<=', $tenureDays);
                })->orWhere(function ($q) use ($tenureMonths) {
                    $q->where('start_type', 'months')->where('start_count', '<=', $tenureMonths);
                })->orWhere(function ($q) use ($tenureYears) {
                    $q->where('start_type', 'years')->where('start_count', '<=', $tenureYears);
                });
            })
            ->orderBy('sequence', 'desc')
            ->orderBy('start_count', 'desc')
            ->first();
    }

    /**
     * Determine if the current date is an accrual date
     */
    protected function isAccrualDate(
        LeaveAllocation $allocation,
        LeaveAccrualLevel $level,
        LeaveAccrualPlan $plan,
        Carbon $date
    ) {
        $lastAccrual = $allocation->last_accrual_date ? Carbon::parse($allocation->last_accrual_date) : null;

        switch ($level->accrual_frequency) {
            case 'daily':
                return true;
            case 'weekly':
                return $date->isSunday();
            case 'biweekly':
                return $date->isSunday() && $date->weekOfYear % 2 == 0;
            case 'monthly':
                $dayToAccrue = $allocation->period_start ? Carbon::parse($allocation->period_start)->day : 1;
                return $date->day == $dayToAccrue && (!$lastAccrual || $lastAccrual->month != $date->month);
            case 'biyearly':
                return ($date->month == 1 || $date->month == 7) && $date->day == 1 && (!$lastAccrual || $lastAccrual->month != $date->month);
            case 'yearly':
                return $date->month == 1 && $date->day == 1 && (!$lastAccrual || $lastAccrual->year != $date->year);
            default:
                return false;
        }
    }

    /**
     * Handle carry-over for an allocation if applicable
     */
    protected function processCarryOverIfDue(LeaveAllocation $allocation, LeaveAccrualPlan $plan, Carbon $date)
    {
        if (!$this->isCarryOverDate($plan, $date)) {
            return;
        }

        $user = $allocation->user;
        $activeLevel = $this->getActiveLevel($user, $plan, $date);

        if (!$activeLevel) {
            return;
        }

        $remaining = (float) $allocation->available_days;
        $carryOver = 0.0;

        switch ($activeLevel->action_with_unused_accruals) {
            case 'all':
                $carryOver = $remaining;
                break;
            case 'maximum':
                $maxCarry = (float) ($activeLevel->max_carryover ?? 0);
                if ($activeLevel->max_carryover_unit === 'hours') {
                    $workingHours = (float) PayrollSetting::get('working_hours_per_day', 8);
                    $maxCarry = $workingHours > 0 ? ($maxCarry / $workingHours) : ($maxCarry / 8);
                }
                $carryOver = min($remaining, $maxCarry);
                break;
            case 'lost':
                $carryOver = 0.0;
                break;
        }

        $allocation->carried_forward = number_format($carryOver, 2, '.', '');
        $allocation->accrued_days = 0.0;
        $allocation->available_days = number_format($carryOver, 2, '.', '');
        $allocation->save();

        Log::info("Processed carry-over for User {$user->id}. Carried forward {$carryOver} days.");
    }

    /**
     * Determine if today is the carry-over date for a plan
     */
    protected function isCarryOverDate(LeaveAccrualPlan $plan, Carbon $date)
    {
        switch ($plan->carry_over_time) {
            case 'year_start':
                return $date->month == 1 && $date->day == 1;
            case 'other':
                return $date->month == $plan->carry_over_month && $date->day == $plan->carry_over_day;
            case 'allocation':
                return $date->month == 1 && $date->day == 1;
            default:
                return false;
        }
    }

    /**
     * Placeholder for worked time modifier
     */
    protected function getWorkedTimeModifier(User $user, LeaveAccrualLevel $level, Carbon $date)
    {
        return 1.0;
    }

    /**
     * Recalculate accruals for all allocations of a specific plan
     */
    public function recalculateForPlan(int $planId): int
    {
        $today = Carbon::today();
        $year = $today->year;
        $processed = 0;

        $plan = LeaveAccrualPlan::find($planId);
        if (!$plan || !$plan->is_active) {
            return $processed;
        }

        $allocations = LeaveAllocation::where('accrual_plan_id', $planId)
            ->where('status', 'approved')
            ->where(function ($query) use ($year) {
                $query->whereYear('period_start', $year)
                    ->orWhereYear('period_end', $year);
            })
            ->get();

        foreach ($allocations as $allocation) {
            $this->processAllocationAccrual($allocation, $plan, $today);
            $processed++;
        }

        Log::info("Recalculated accruals for plan #{$planId} ({$plan->name}). Processed {$processed} allocations.");

        return $processed;
    }
}

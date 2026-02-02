<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveAllocation;
use App\Models\User;
use Carbon\Carbon;

class LeaveAccrualCommand extends Command
{
    protected $signature = 'leave:process-accruals';
    protected $description = 'Process leave accruals for active plans';

    public function handle()
    {
        $this->info('Processing leave accruals...');
        
        $plans = LeaveAccrualPlan::where('is_active', true)->get();
        $today = Carbon::today();
        $processedCount = 0;

        foreach ($plans as $plan) {
            $users = $this->getEligibleUsers($plan);
            
            foreach ($users as $user) {
                if ($this->shouldAccrue($plan, $user, $today)) {
                    $this->processAccrual($plan, $user, $today);
                    $processedCount++;
                }
            }
        }

        $this->info("Processed {$processedCount} accruals successfully.");
        return 0;
    }

    protected function getEligibleUsers($plan)
    {
        return User::where('is_active', true)->get();
    }

    protected function shouldAccrue($plan, $user, $today)
    {
        $hireDate = $user->created_at;
        $waitingPeriod = $plan->waiting_period_days ?? 0;
        
        if ($hireDate->addDays($waitingPeriod)->isFuture()) {
            return false;
        }

        $lastAccrual = LeaveAllocation::where('user_id', $user->id)
            ->where('leave_type_id', $plan->leave_type_id)
            ->where('allocation_type', 'accrual')
            ->latest('created_at')
            ->first();

        if (!$lastAccrual) {
            return true;
        }

        $nextAccrualDate = $this->calculateNextAccrualDate($lastAccrual->created_at, $plan->accrual_frequency);
        
        return $today->gte($nextAccrualDate);
    }

    protected function calculateNextAccrualDate($lastDate, $frequency)
    {
        $date = Carbon::parse($lastDate);
        
        return match($frequency) {
            'monthly' => $date->addMonth(),
            'yearly' => $date->addYear(),
            'per_pay_period' => $date->addWeeks(2),
            default => $date->addMonth(),
        };
    }

    protected function processAccrual($plan, $user, $today)
    {
        $accrualAmount = $plan->accrual_rate;
        
        if ($plan->prorate_on_join && !$this->hasAccruedBefore($user, $plan)) {
            $accrualAmount = $this->calculateProratedAmount($user, $plan, $today);
        }

        // Find or create allocation for this year
        $allocation = LeaveAllocation::firstOrNew([
            'user_id' => $user->id,
            'leave_type_id' => $plan->leave_type_id,
            'year' => $today->year,
        ]);

        $currentTotal = $allocation->available_days ?? 0;

        if ($plan->max_accrual_days && ($currentTotal + $accrualAmount) > $plan->max_accrual_days) {
            $accrualAmount = max(0, $plan->max_accrual_days - $currentTotal);
        }

        if ($accrualAmount > 0) {
            $allocation->allocated_days = ($allocation->allocated_days ?? 0) + $accrualAmount;
            $allocation->available_days = ($allocation->available_days ?? 0) + $accrualAmount;
            $allocation->used_days = $allocation->used_days ?? 0;
            $allocation->allocation_type = 'accrual';
            $allocation->last_accrual_date = $today;
            $allocation->notes = $allocation->exists 
                ? $allocation->notes . " | Accrued {$accrualAmount} days on {$today->format('Y-m-d')}"
                : "Auto-accrued via {$plan->name}";
            $allocation->save();
        }
    }

    protected function hasAccruedBefore($user, $plan)
    {
        return LeaveAllocation::where('user_id', $user->id)
            ->where('leave_type_id', $plan->leave_type_id)
            ->where('allocation_type', 'accrual')
            ->exists();
    }

    protected function calculateProratedAmount($user, $plan, $today)
    {
        $hireDate = $user->created_at;
        $monthStart = $today->copy()->startOfMonth();
        $daysInMonth = $today->daysInMonth;
        $daysWorked = $monthStart->diffInDays(min($today, $monthStart->copy()->endOfMonth())) + 1;
        
        return ($plan->accrual_rate / $daysInMonth) * $daysWorked;
    }
}

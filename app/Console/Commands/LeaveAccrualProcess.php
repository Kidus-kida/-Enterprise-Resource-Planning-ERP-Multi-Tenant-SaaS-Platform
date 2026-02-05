<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveAllocation;
use Carbon\Carbon;

class LeaveAccrualProcess extends Command
{
    protected $signature = 'leave:process-accruals {--frequency= : Filter by frequency (monthly, yearly)} {--dry-run : Simulate without changes}';
    protected $description = 'Process leave accruals based on active plans';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $frequencyFilter = $this->option('frequency');
        
        $this->info("Starting Leave Accrual Process" . ($dryRun ? " (DRY RUN)" : ""));

        $plans = LeaveAccrualPlan::where('is_active', true)
            ->when($frequencyFilter, fn($q) => $q->where('accrual_frequency', $frequencyFilter))
            ->get();

        foreach ($plans as $plan) {
            if (!$this->shouldRunToday($plan)) {
                $this->info("Skipping Plan: {$plan->name} (Not due today)");
                continue;
            }

            $this->processPlan($plan, $dryRun);
        }
        
        $this->info("Accrual Process Completed.");
    }

    private function shouldRunToday(LeaveAccrualPlan $plan): bool
    {
        $today = Carbon::today();
        
        if ($plan->accrual_frequency === 'monthly') {
            // Run on the 1st of the month
            return $today->day === 1; 
        }

        if ($plan->accrual_frequency === 'yearly') {
            // Run on Jan 1st
            return $today->dayOfYear === 1;
        }

        // 'per_pay_period' requires payroll schedule integration, skipping for now
        return false; 
    }

    private function processPlan(LeaveAccrualPlan $plan, bool $dryRun)
    {
        $this->info("Processing Plan: {$plan->name} ({$plan->accrual_frequency})");
        
        $users = User::where('status', 'active')->get(); // Adjust status check as per User model

        foreach ($users as $user) {
            // Check Eligibility (Waiting Period)
            if ($plan->waiting_period_days > 0) {
                // Determine joining date (assuming created_at or needs a specific field)
                $joinDate = $user->created_at; // Or $user->employeeDetail->joining_date
                if ($joinDate->diffInDays(Carbon::today()) < $plan->waiting_period_days) {
                    continue; // In waiting period
                }
            }

            // Calculate Amount
            $amount = $plan->accrual_rate;
            
            // Proration Logic (Simple: if joined this month, maybe skip or partial? Defaulting to full for simplicity)
            
            if ($dryRun) {
                $this->line("  [DRY] Would accrue {$amount} days for {$user->name} ({$plan->leaveType->name})");
                continue;
            }

            // Find or Create Allocation for THIS YEAR
            $allocation = LeaveAllocation::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'leave_type_id' => $plan->leave_type_id,
                    'year' => Carbon::now()->year,
                ],
                [
                    'allocated_days' => 0,
                    'available_days' => 0,
                    'used_days' => 0,
                    'allocation_type' => 'accrual',
                    'status' => 'approved',
                    'allocated_by' => null, // System
                ]
            );

            // Check Max Accrual Cap
            if ($plan->max_accrual_days > 0 && ($allocation->allocated_days + $amount) > $plan->max_accrual_days) {
                $amount = max(0, $plan->max_accrual_days - $allocation->allocated_days);
            }

            if ($amount > 0) {
                $allocation->increment('allocated_days', $amount);
                $allocation->increment('available_days', $amount);
                // Log/Note update?
            }
        }
    }
}

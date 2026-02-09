<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\LeaveAccrualPlan;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Annual Leave
        $annualLeave = LeaveType::create([
            'type_name' => 'Annual Leave',
            'description' => 'Standard annual vacation leave',
            'color' => '#4CAF50',
            'status' => 'active',
            'is_active' => true,
            'uses_accrual' => true,
            'requires_approval' => true,
            'approval_levels' => 2,
            'allow_half_day' => true,
            'is_paid' => true,
            'min_days_notice' => 3,
            'max_consecutive_days' => 30,
            'allow_negative_balance' => false,
            'max_negative_balance' => 0,
            'can_carry_forward' => true,
            'max_carry_forward' => 5,
            'carry_forward_expiry' => 3,
            'sort_order' => 1,
        ]);

        // Create Accrual Plan for Annual Leave
        LeaveAccrualPlan::create([
            'name' => 'Standard Annual Leave Accrual',
            'leave_type_id' => $annualLeave->id,
            'accrual_frequency' => 'monthly',
            'accrual_rate' => 1.25,
            'max_accrual_days' => 15,
            'waiting_period_days' => 90,
            'prorate_on_join' => true,
            'allow_carryover' => true,
            'max_carryover_days' => 5,
            'carryover_expiry_date' => now()->endOfYear()->addMonths(3),
            'allow_negative_balance' => false,
            'max_negative_days' => 0,
            'is_active' => true,
            'description' => 'Accrues 1.25 days per month (15 days/year)',
        ]);

        // Sick Leave
        $sickLeave = LeaveType::create([
            'type_name' => 'Sick Leave',
            'description' => 'Medical leave for illness or injury',
            'color' => '#FF9800',
            'status' => 'active',
            'is_active' => true,
            'uses_accrual' => true,
            'requires_approval' => true,
            'approval_levels' => 1,
            'allow_half_day' => true,
            'is_paid' => true,
            'min_days_notice' => 0,
            'max_consecutive_days' => 60,
            'requires_attachment' => true,
            'allow_negative_balance' => true,
            'max_negative_balance' => 3,
            'can_carry_forward' => false,
            'max_carry_forward' => 0,
            'carry_forward_expiry' => null,
            'sort_order' => 2,
        ]);

        // Create Accrual Plan for Sick Leave
        LeaveAccrualPlan::create([
            'name' => 'Standard Sick Leave Accrual',
            'leave_type_id' => $sickLeave->id,
            'accrual_frequency' => 'monthly',
            'accrual_rate' => 0.83,
            'max_accrual_days' => 10,
            'waiting_period_days' => 0,
            'prorate_on_join' => true,
            'allow_carryover' => false,
            'max_carryover_days' => 0,
            'carryover_expiry_date' => null,
            'allow_negative_balance' => true,
            'max_negative_days' => 3,
            'is_active' => true,
            'description' => 'Accrues 0.83 days per month (10 days/year)',
        ]);

        // Unpaid Leave
        LeaveType::create([
            'type_name' => 'Unpaid Leave',
            'description' => 'Leave without pay',
            'color' => '#9E9E9E',
            'status' => 'active',
            'is_active' => true,
            'uses_accrual' => false,
            'requires_approval' => true,
            'approval_levels' => 2,
            'allow_half_day' => false,
            'is_paid' => false,
            'min_days_notice' => 7,
            'max_consecutive_days' => 90,
            'allow_negative_balance' => false,
            'max_negative_balance' => 0,
            'can_carry_forward' => false,
            'max_carry_forward' => 0,
            'carry_forward_expiry' => null,
            'sort_order' => 3,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends TenantModel
{
    protected $fillable = [
        'type_name',
        'description',
        'default_accrual_plan_id',

        // Time Off Logic
        'duration_type',
        'count_as',
        'leave_allowed_interval',

        // Availability & Visibility
        'max_date_allowed',
        'ignore_public_holidays',
        'hide_on_dashboard',
        'eligible_for_accrual',

        // Notification
        'notify_hr',
        'hr_notification_recipients',

        // Allocation Requests
        'requires_allocation',
        'employee_requests_allowed',
        'allocation_approval_levels',

        // Leave Behavior (Requests)
        'requires_attachment',
        'min_days_notice',
        'max_consecutive_days',
        'allow_half_day',
        'is_paid',

        // Request Approval Settings
        'requires_approval',
        'approval_levels',
        'auto_approve_if_balance',

        // Balance Settings
        'allow_negative_balance',
        'max_negative_balance',
        'can_carry_forward',
        'max_carry_forward',
        'carry_forward_expiry',

        'active',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'ignore_public_holidays' => 'boolean',
        'hide_on_dashboard' => 'boolean',
        'eligible_for_accrual' => 'boolean',
        'notify_hr' => 'boolean',
        'hr_notification_recipients' => 'array',
        'requires_allocation' => 'boolean',
        'employee_requests_allowed' => 'boolean',
        'requires_attachment' => 'boolean',
        'allow_half_day' => 'boolean',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'auto_approve_if_balance' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'can_carry_forward' => 'boolean',
        'active' => 'boolean',
    ];

    public function accrualPlan()
    {
        return $this->belongsTo(LeaveAccrualPlan::class, 'default_accrual_plan_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }


}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends TenantModel
{
    protected $fillable = [
        'type_name',
        'max_date_allowed',
        'leave_allowed_interval',
        'description',
        'status',
        // New Accreditation Fields
        'uses_accrual',
        'default_accrual_plan_id',
        'requires_attachment',
        'min_days_notice',
        'max_consecutive_days',
        'allow_half_day',
        'is_paid',
        'requires_approval',
        'approval_levels',
        'auto_approve_if_balance',
        'color',
        'sort_order',
        'is_active',
        // Negative Balance & Carryover
        'allow_negative_balance',
        'max_negative_balance',
        'can_carry_forward',
        'max_carry_forward',
        'carry_forward_expiry',
    ];

    protected $casts = [
        'uses_accrual' => 'boolean',
        'requires_attachment' => 'boolean',
        'allow_half_day' => 'boolean',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'auto_approve_if_balance' => 'boolean',
        'is_active' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'can_carry_forward' => 'boolean',
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


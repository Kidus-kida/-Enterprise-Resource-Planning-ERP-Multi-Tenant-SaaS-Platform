<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
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
    ];

    protected $casts = [
        'uses_accrual' => 'boolean',
        'requires_attachment' => 'boolean',
        'allow_half_day' => 'boolean',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'auto_approve_if_balance' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function accrualPlan()
    {
        return $this->belongsTo(LeaveAccrualPlan::class, 'default_accrual_plan_id');
    }


}

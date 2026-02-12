<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveAccrualPlan extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'accrued_gain_time',
        'carry_over_time',
        'carry_over_day',
        'carry_over_month',
        'is_based_on_worked_time',
        'transition_mode',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_based_on_worked_time' => 'boolean',
        'is_active' => 'boolean',
        'carry_over_day' => 'integer',
        'carry_over_month' => 'integer',
    ];

    /**
     * Get the milestones/levels for this plan
     */
    public function levels(): HasMany
    {
        return $this->hasMany(LeaveAccrualLevel::class, 'leave_accrual_plan_id')->orderBy('sequence');
    }

    /**
     * Get all leave types using this plan
     */
    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class, 'default_accrual_plan_id');
    }

    /**
     * Get all allocations using this plan
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(LeaveAllocation::class, 'accrual_plan_id');
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}


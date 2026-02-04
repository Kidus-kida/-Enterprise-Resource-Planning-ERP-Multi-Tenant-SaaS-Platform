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
        'leave_type_id',
        'accrual_frequency',
        'accrual_rate',
        'max_accrual_days',
        'waiting_period_days',
        'prorate_on_join',
        'allow_carryover',
        'max_carryover_days',
        'carryover_expiry_date',
        'allow_negative_balance',
        'max_negative_days',
        'is_active',
        'description',
    ];

    protected $casts = [
        'accrual_rate' => 'decimal:2',
        'prorate_on_join' => 'boolean',
        'allow_carryover' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'is_active' => 'boolean',
        'carryover_expiry_date' => 'date',
    ];

    /**
     * Get the leave type that owns this accrual plan
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
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

    /**
     * Calculate accrual for a given period
     */
    public function calculateAccrual(int $months = 1): float
    {
        if ($this->accrual_frequency === 'monthly') {
            return (float) $this->accrual_rate * $months;
        } elseif ($this->accrual_frequency === 'yearly') {
            return (float) $this->accrual_rate;
        }
        
        return 0;
    }

    /**
     * Check if carryover is allowed
     */
    public function canCarryover(): bool
    {
        return $this->allow_carryover;
    }

    /**
     * Get maximum carryover days
     */
    public function getMaxCarryoverDays(): ?int
    {
        return $this->max_carryover_days;
    }
}


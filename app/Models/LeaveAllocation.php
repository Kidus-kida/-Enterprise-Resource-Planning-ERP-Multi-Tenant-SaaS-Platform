<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'accrual_plan_id',
        'year',
        'period_start',
        'period_end',
        'opening_balance',
        'allocated_days',
        'accrued_days',
        'used_days',
        'pending_days',
        'available_days',
        'carried_forward',
        'last_accrual_date',
        'notes',
        'is_manual_allocation',
        'allocated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'allocated_days' => 'decimal:2',
        'accrued_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'available_days' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'last_accrual_date' => 'date',
        'is_manual_allocation' => 'boolean',
    ];

    /**
     * Get the user that owns this allocation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the accrual plan
     */
    public function accrualPlan(): BelongsTo
    {
        return $this->belongsTo(LeaveAccrualPlan::class);
    }

    /**
     * Get the user who allocated (for manual allocations)
     */
    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    /**
     * Scope for current year
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Update available balance
     */
    public function updateAvailableBalance(): void
    {
        $this->available_days = $this->opening_balance 
            + $this->allocated_days 
            + $this->accrued_days 
            + $this->carried_forward 
            - $this->used_days 
            - $this->pending_days;
        
        $this->save();
    }

    /**
     * Add accrued days
     */
    public function addAccrual(float $days): void
    {
        $this->accrued_days += $days;
        $this->last_accrual_date = now();
        $this->updateAvailableBalance();
    }

    /**
     * Deduct used days
     */
    public function deductUsedDays(float $days): void
    {
        $this->used_days += $days;
        $this->updateAvailableBalance();
    }

    /**
     * Add pending days
     */
    public function addPendingDays(float $days): void
    {
        $this->pending_days += $days;
        $this->updateAvailableBalance();
    }

    /**
     * Remove pending days (when request is approved/rejected)
     */
    public function removePendingDays(float $days): void
    {
        $this->pending_days -= $days;
        $this->updateAvailableBalance();
    }
}

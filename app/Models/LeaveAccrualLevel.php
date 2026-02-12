<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAccrualLevel extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'leave_accrual_plan_id',
        'sequence',
        'start_count',
        'start_type',
        'accrual_amount',
        'accrual_frequency',
        'cap_accrued_time',
        'action_with_unused_accruals',
        'max_carryover',
        'carryover_validity_period',
        'yearly_cap',
        'accrual_unit',
        'max_carryover_unit',
        'yearly_cap_unit',
        'balance_cap_unit',
    ];

    protected $casts = [
        'accrual_amount' => 'decimal:4',
        'cap_accrued_time' => 'decimal:4',
        'max_carryover' => 'decimal:4',
        'sequence' => 'integer',
        'start_count' => 'integer',
        'carryover_validity_period' => 'integer',
        'yearly_cap' => 'decimal:4',
        'accrual_unit' => 'string',
        'max_carryover_unit' => 'string',
        'yearly_cap_unit' => 'string',
        'balance_cap_unit' => 'string',
    ];

    /**
     * Get the accrual plan that owns this level
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(LeaveAccrualPlan::class, 'leave_accrual_plan_id');
    }
}

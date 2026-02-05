<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePolicyEvent extends TenantModel
{
    protected $fillable = [
        'attendance_timestamp_id',
        'user_id',
        'event_type',
        'policy_type',
        'status',
        'is_violation',
        'penalty_amount',
        'message',
        'metadata',
        'evaluated_at',
        'evaluated_by',
    ];

    protected $casts = [
        'is_violation' => 'boolean',
        'penalty_amount' => 'decimal:2',
        'metadata' => 'array',
        'evaluated_at' => 'datetime',
    ];

    /**
     * Get the attendance timestamp
     */
    public function timestamp(): BelongsTo
    {
        return $this->belongsTo(AttendanceTimestamp::class, 'attendance_timestamp_id');
    }

    /**
     * Get the user (employee)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the evaluator (admin/manager)
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Scope: Filter by policy type
     */
    public function scopeOfType($query, string $policyType)
    {
        return $query->where('policy_type', $policyType);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter violations only
     */
    public function scopeViolations($query)
    {
        return $query->where('is_violation', true);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Mark event as applied
     */
    public function markAsApplied(): void
    {
        $this->update(['status' => 'applied']);
    }

    /**
     * Mark event as waived
     */
    public function waive(?int $waivedBy = null): void
    {
        $this->update([
            'status' => 'waived',
            'evaluated_by' => $waivedBy ?? auth()->id(),
        ]);
    }
}


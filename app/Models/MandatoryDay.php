<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MandatoryDay extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'description',
        'restriction_type',
        'restriction_message',
        'applicable_departments',
        'applicable_designations',
        'excluded_users',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'applicable_departments' => 'array',
        'applicable_designations' => 'array',
        'excluded_users' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this mandatory day
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active mandatory days
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get upcoming mandatory days
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->orderBy('date', 'asc');
    }

    /**
     * Scope to get mandatory days for a specific date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if a user is affected by this mandatory day
     */
    public function affectsUser(User $user): bool
    {
        // Check if user is excluded
        if ($this->excluded_users && in_array($user->id, $this->excluded_users)) {
            return false;
        }

        // If no specific departments/designations, affects everyone
        if (!$this->applicable_departments && !$this->applicable_designations) {
            return true;
        }

        // Check department
        if ($this->applicable_departments && $user->department_id) {
            if (in_array($user->department_id, $this->applicable_departments)) {
                return true;
            }
        }

        // Check designation
        if ($this->applicable_designations && $user->designation_id) {
            if (in_array($user->designation_id, $this->applicable_designations)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get restriction message for display
     */
    public function getRestrictionMessageAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return match($this->restriction_type) {
            'no_leave' => 'Leave requests are not allowed on this day.',
            'requires_approval' => 'Leave requests on this day require special approval.',
            'warning_only' => 'Please note: This is a mandatory work day.',
            default => 'This is a mandatory work day.',
        };
    }

    /**
     * Check if leave is completely blocked
     */
    public function isLeaveBlocked(): bool
    {
        return $this->restriction_type === 'no_leave';
    }
}


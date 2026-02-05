<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissedPunchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'punch_type',
        'requested_start_time',
        'requested_end_time',
        'reason',
        'status',
        'rejection_reason',
        'approver_id',
        'approved_at',
        'original_data',
    ];

    protected $casts = [
        'date' => 'date',
        'requested_start_time' => 'datetime:H:i',
        'requested_end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'original_data' => 'array',
    ];

    /**
     * Get the employee who made the request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attendance record being corrected, if any
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope: Pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

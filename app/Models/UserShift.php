<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserShift extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope: Active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Effective on a given date
     */
    public function scopeEffectiveOn($query, string $date)
    {
        $date = Carbon::parse($date);

        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date);
            });
    }

    /**
     * Check if this assignment is effective on a given date
     */
    public function isEffectiveOn(string $date): bool
    {
        $date = Carbon::parse($date);

        if ($date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return $this->is_active;
    }

    /**
     * Get the shift for a specific user on a specific date
     * 
     * @param int $userId
     * @param string $date
     * @return Shift|null
     */
    public static function getShiftForUser(int $userId, string $date): ?Shift
    {
        $assignment = self::where('user_id', $userId)
            ->active()
            ->effectiveOn($date)
            ->with('shift')
            ->first();

        return $assignment?->shift;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftOverride extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'shift_id',
        'date',
        'is_active',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Company::class);
    }
}

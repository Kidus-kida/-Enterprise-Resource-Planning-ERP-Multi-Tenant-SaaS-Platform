<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:4',
    ];

    /**
     * Get the account for this journal entry
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the user who created this journal entry
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter by business
     */
    public function scopeForBusiness($query, $business_id)
    {
        return $query->where('business_id', $business_id);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $start_date, $end_date)
    {
        if ($start_date) {
            $query->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $query->where('date', '<=', $end_date);
        }
        return $query;
    }

    /**
     * Scope to filter by type (debit/credit)
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}

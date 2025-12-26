<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'operation_date' => 'date',
    ];

    /**
     * Relationship: Account
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Relationship: Related Transaction (if linked to a transaction)
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Models\Transaction::class, 'transaction_id');
    }

    /**
     * Get transactions by type and date range
     */
    public static function getByTypeAndDateRange($account_id, $type, $start_date = null, $end_date = null)
    {
        $query = self::where('account_id', $account_id)
            ->where('type', $type);

        if ($start_date) {
            $query->where('operation_date', '>=', $start_date);
        }

        if ($end_date) {
            $query->where('operation_date', '<=', $end_date);
        }

        return $query->get();
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 'debit');
    }
}

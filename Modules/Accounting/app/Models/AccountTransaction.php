<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'operation_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function transaction()
    {
        return $this->belongsTo(\App\Models\Transaction::class, 'transaction_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get account balance by type
     */
    public static function getAccountBalanceByType(
        $account_id, 
        $type, 
        $start_date, 
        $end_date, 
        $is_previous = false, 
        $opening_balance_only = false,
        $sub_type = null
    ) {
        $balance = self::leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
            ->leftjoin('transactions', 'account_transactions.transaction_id', 'transactions.id')
            ->where('accounts.id', $account_id)
            ->where('accounts.is_main_account', 0)
            ->where('account_transactions.type', $type);

        if ($opening_balance_only) {
            $balance->whereIn('transactions.type', ['opening_balance'])
                ->where('transactions.final_total', '>', 0);
        } else {
            $balance->whereNotIn('transactions.type', ['opening_balance']);
        }

        $amount = 0;
        if (!$opening_balance_only) {
            if (!$is_previous) {
                $balance->whereDate('operation_date', '>=', $start_date);
                $balance->whereDate('operation_date', '<=', $end_date);
            } else {
                $balance->whereDate('operation_date', '<', $start_date);
            }
        }

        if (strlen($sub_type) > 0) {
            $balance->where('account_transactions.sub_type', $sub_type);
        }
        
        $amount = $balance->sum('amount');
        
        return !empty($amount) ? $amount : 0;
    }
}

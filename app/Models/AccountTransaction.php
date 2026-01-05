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

    public static function createAccountTransaction($data)
    {
        if (empty($data['account_id'])) {
            return null;
        }

        $business_id = !empty($data['business_id']) ? $data['business_id'] : request()->session()->get('user.business_id');

        $transaction_data = [
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'business_id' => $business_id,
            'transaction_type' => !empty($data['transaction_type']) ? $data['transaction_type'] : ($data['type'] ?? 'debit'),
            'transaction_date' => !empty($data['operation_date']) ? $data['operation_date'] : (\Carbon\Carbon::now()),
            'created_by' => !empty($data['created_by']) ? $data['created_by'] : auth()->id(),
            'description' => !empty($data['note']) ? $data['note'] : null,
            'reference_no' => !empty($data['cheque_number']) ? $data['cheque_number'] : (!empty($data['reference_no']) ? $data['reference_no'] : null),
            'reference_type' => !empty($data['sub_type']) ? $data['sub_type'] : null,
            'reference_id' => !empty($data['reference_id']) ? $data['reference_id'] : null,
        ];

        $account_transaction = self::create($transaction_data);

        return $account_transaction;
    }

    /**
     * Get transactions by type and date range
     */
    public static function getByTypeAndDateRange($account_id, $type, $start_date = null, $end_date = null)
    {
        $query = self::where('account_id', $account_id)
            ->where('transaction_type', $type);

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
        return $query->where('transaction_type', 'credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebit($query)
    {
        return $query->where('transaction_type', 'debit');
    }
}

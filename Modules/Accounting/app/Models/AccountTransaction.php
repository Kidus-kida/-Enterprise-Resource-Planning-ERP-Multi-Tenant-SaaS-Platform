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
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($account_transaction) {
            $account = Account::find($account_transaction->account_id);
            if ($account) {
                if ($account_transaction->type == 'debit') {
                    $account->current_balance += $account_transaction->amount;
                } else {
                    $account->current_balance -= $account_transaction->amount;
                }
                $account->save();
            }
        });

        static::deleted(function ($account_transaction) {
            $account = Account::find($account_transaction->account_id);
            if ($account) {
                if ($account_transaction->type == 'debit') {
                    $account->current_balance -= $account_transaction->amount;
                } else {
                    $account->current_balance += $account_transaction->amount;
                }
                $account->save();
            }
        });
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
            'type' => $data['type'],
            'sub_type' => !empty($data['sub_type']) ? $data['sub_type'] : null,
            'operation_date' => !empty($data['operation_date']) ? $data['operation_date'] : \Carbon::now(),
            'created_by' =>  !empty($data['created_by']) ? $data['created_by'] : auth()->id(),
            'transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : null,
            'transaction_payment_id' => !empty($data['transaction_payment_id']) ? $data['transaction_payment_id'] : null,
            'note' => !empty($data['note']) ? $data['note'] : null,
            'attachment' => !empty($data['attachment']) ? $data['attachment'] : null,
            'transfer_transaction_id' => !empty($data['transfer_transaction_id']) ? $data['transfer_transaction_id'] : null,
            'transaction_sell_line_id' => !empty($data['transaction_sell_line_id']) ? $data['transaction_sell_line_id'] : null,
            'sell_line_id' => !empty($data['sell_line_id']) ? $data['sell_line_id'] : null,
            'purchase_line_id' => !empty($data['purchase_line_id']) ? $data['purchase_line_id'] : null,
            'income_type' => !empty($data['income_type']) ? $data['income_type'] : null,
            'installment_id' => !empty($data['installment_id']) ? $data['installment_id'] : null,
            'fixed_asset_id' => !empty($data['fixed_asset_id']) ? $data['fixed_asset_id'] : null,
            
        ];
        
        // Ensure transaction_type is set (required by schema)
        if (!isset($transaction_data['transaction_type'])) {
             $transaction_data['transaction_type'] = $data['type'];
        }

        $account_transaction = AccountTransaction::create($transaction_data);

        return $account_transaction;
    }
}

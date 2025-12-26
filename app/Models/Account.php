<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_closed' => 'boolean',
        'is_main_account' => 'boolean',
        'visible' => 'boolean',
        'disabled' => 'boolean',
    ];

    /**
     * Get dropdown options for accounts
     */
    public static function forDropdown($prepend_none = false, $closed = false)
    {
        $query = self::query();

        if (!$closed) {
            $query->where('is_closed', 0);
        }

        $dropdown = $query->pluck('name', 'id');
        
        if ($prepend_none) {
            $dropdown->prepend(__('None'), '');
        }

        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Relationship: Account Type
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    /**
     * Relationship: Creator/User
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relationship: Account Group
     */
    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'asset_type', 'id');
    }

    /**
     * Relationship: Sub Accounts
     */
    public function subAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Relationship: Parent Account
     */
    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Relationship: Account Transactions
     */
    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id');
    }

    /**
     * Get account by account name
     */
    public static function getAccountByName($account_name)
    {
        return self::where('name', $account_name)->first();
    }

    /**
     * Check if account has insufficient balance warnings enabled
     */
    public static function checkInsufficientBalance($id)
    {
        $account = self::find($id);
        if (!$account) {
            return false;
        }

        $account_group = $account->accountGroup;
        $check_insufficient = false;
        
        if (!empty($account_group)) {
            if (in_array($account_group->name, ['Cash Account', "Cheques in Hand (Customer's)", 'Card'])) {
                $check_insufficient = true;
            }
        }
        
        return $check_insufficient;
    }

    /**
     * Calculate account balance
     */
    public static function getAccountBalance($id, $start_date = null, $end_date = null, $get_previous = false)
    {
        $account = self::find($id);
        if (!$account) {
            return 0;
        }

        $account_type = $account->accountType;
        $account_type_name = !empty($account_type) ? $account_type->name : "";

        $query = AccountTransaction::where('account_id', $id);

        if (!empty($start_date) && !$get_previous) {
            $query->where('operation_date', '>=', $start_date);
        }

        if (!empty($end_date) && !$get_previous) {
            $query->where('operation_date', '<=', $end_date);
        }

        if ($get_previous && !empty($start_date)) {
            $query->whereDate('operation_date', '<=', $start_date);
        }

        $result = $query->select(
            DB::raw("SUM(IF(type='credit', amount, 0)) as creditSum"),
            DB::raw("SUM(IF(type='debit', amount, 0)) as debitSum")
        )->first();

        // Calculate balance based on account type
        if (in_array($account_type_name, ["Assets", "Expenses", "Current Assets", "Fixed Assets"])) {
            $balance = $result->debitSum - $result->creditSum;
        } elseif (in_array($account_type_name, ["Liabilities", "Equity", "Income", "Long Term Liabilities", "Current Liabilities"])) {
            $balance = $result->creditSum - $result->debitSum;
        } else {
            $balance = $result->debitSum - $result->creditSum;
        }

        return $balance;
    }

    /**
     * Get sub account balance by main account ID
     */
    public static function getSubAccountBalance($parent_account_id, $start_date = null, $end_date = null)
    {
        $accounts = self::where('parent_account_id', $parent_account_id)
            ->select(['name', 'id', 'account_number'])
            ->get();

        $balance = 0;
        foreach ($accounts as $account) {
            $balance += self::getAccountBalance($account->id, $start_date, $end_date);
        }
        
        return round($balance, 2);
    }
}

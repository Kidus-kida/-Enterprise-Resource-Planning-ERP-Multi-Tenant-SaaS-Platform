<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }
    

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'asset_type');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function subAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id');
    }
 

    /**
     * Scopes
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Static Helper Methods
     */
    public static function forDropdown($business_id, $prepend_none = false, $closed = false)
    {
        $query = self::where('business_id', $business_id);

        if (!$closed) {
            $query->where('is_closed', 0);
        }

        $dropdown = $query->pluck('name', 'id');

        if ($prepend_none) {
            $dropdown->prepend(__('None'), '');
        }

        return $dropdown;
    }

    public static function accountTypes()
    {
        return [
            '' => __('Not Applicable'),
            'saving_current' => __('Saving/Current'),
            'capital' => __('Capital')
        ];
    }

    /**
     * Get account balance
     * 
     * @param int $id Account ID
     * @param string|null $start_date Start date
     * @param string|null $end_date End date
     * @param bool $get_previous Get previous balance
     * @param bool $account_book For account book
     * @param bool $is_daily_report For daily report
     * @return float Account balance
     */
    public static function getAccountBalance(
        $id,
        $start_date = null,
        $end_date = null,
        $get_previous = false,
        $account_book = false,
        $is_daily_report = false
    ) {
        $account = self::find($id);
        if (!$account) {
            return 0;
        }

        $account_type_name = optional($account->accountType)->name ?? "";
        // $business_id = session()->get('user.business_id');

        $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
        // dd($business_id);

        $account_query = self::leftjoin('account_transactions as AT', 'AT.account_id', '=', 'accounts.id')
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id);

        if (!empty($start_date) && !$get_previous) {
            $account_query->where('operation_date', '>=', $start_date);
        }

        if (!empty($end_date) && !$get_previous) {
            $account_query->where('operation_date', '<=', $end_date);
        }

        if ($get_previous && !empty($start_date)) {
            if ($account_book) {
                $account_query->whereDate('operation_date', '<', $start_date);
            } elseif ($is_daily_report) {
                $account_query->whereDate('operation_date', '<=', $end_date);
            } else {
                $account_query->whereDate('operation_date', '<=', $start_date);
            }
        }

        $account_query->where('is_closed', 0);

        $result = $account_query->select(
            DB::raw("SUM(IF(AT.type='credit',current_balance, 0)) as creditSum"),
            DB::raw("SUM(IF(AT.type='debit', current_balance, 0)) as debitSum")
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
     * Check if account requires insufficient balance check
     */
    public static function checkInsufficientBalance($id)
    {
        $account = self::find($id);
        if (!$account || !$account->accountGroup) {
            return false;
        }

        $check_insufficient = false;
        $group_name = $account->accountGroup->name;

        if (in_array($group_name, ['Cash Account', "Cheques in Hand (Customer's)", 'Card'])) {
            $check_insufficient = true;
        }

        return $check_insufficient;
    }

    /**
     * Get account by account type ID
     */
    public static function getAccountByAccountTypeId($account_type_id)
    {
        $business_id = request()->session()->get('user.business_id');

        return self::where('business_id', $business_id)
            ->where('account_type_id', $account_type_id)
            ->where('is_main_account', 0)
            ->pluck('name', 'id');
    }

    /**
     * Get account by group ID
     */
    public static function getAccountByAccountGroupId($group_id, $is_main_account = 0)
    {
        $business_id = request()->session()->get('user.business_id');

        return self::where('business_id', $business_id)
            ->where('asset_type', $group_id)
            ->where('is_main_account', $is_main_account)
            ->pluck('name', 'id');
    }

    /**
     * Get sub-account balance by main account ID
     */
    public static function getSubAccountBalanceByMainAccountId($parent_account_id, $start_date = null, $end_date = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $accounts = self::where('parent_account_id', $parent_account_id)
            ->where('business_id', $business_id)
            ->select(['name', 'id', 'account_number'])
            ->get();

        $balance = 0;
        foreach ($accounts as $account) {
            $balance += self::getAccountBalance($account->id, null, $end_date);
        }

        return round($balance, 2);
    }

    /**
     * Create post-dated cheques accounts if not exist
     */
    public static function createPostdatedChequesAccount($business_id, $user_id)
    {
        $account_type = AccountType::getAccountTypeIdByName('Current Assets', $business_id, true);
        $account_group = AccountGroup::getGroupByName('Bank Account', true);

        // Create 'Post Dated Cheques' account
        $account_pdc = self::where('business_id', $business_id)
            ->where('name', 'Post Dated Cheques')
            ->first();

        if (empty($account_pdc)) {
            $account = new self;
            $account->business_id = $business_id;
            $account->name = "Post Dated Cheques";
            $account->account_number = rand(111111, 999999);
            $account->account_type_id = $account_type;
            $account->asset_type = $account_group;
            $account->is_need_cheque = "N";
            $account->created_by = $user_id;
            $account->is_main_account = 0;
            $account->is_closed = 0;
            $account->visible = 1;
            $account->disabled = 0;
            $account->save();
        }

        // Create 'Issued Post Dated Cheques' account
        $issued_pdc = self::where('business_id', $business_id)
            ->where('name', 'Issued Post Dated Cheques')
            ->first();

        if (empty($issued_pdc)) {
            $account = new self;
            $account->business_id = $business_id;
            $account->name = "Issued Post Dated Cheques";
            $account->account_number = rand(111111, 999999);
            $account->account_type_id = AccountType::getAccountTypeIdByName('Current Liabilities', $business_id, true);
            $account->asset_type = AccountGroup::getGroupByName('Bank Account', true);
            $account->is_need_cheque = "N";
            $account->created_by = $user_id;
            $account->is_main_account = 0;
            $account->is_closed = 0;
            $account->visible = 1;
            $account->disabled = 0;
            $account->save();
        }

        return "Ok";
    }

    public static function getAccountByAccountName($account_name)
    {
        $business_id = request()->session()->get('business.id') ?? 1;
        $account = Account::where(DB::raw("REPLACE(`name`, '  ', ' ')"), $account_name)->where('business_id', $business_id)->first();

        return $account;
    }
}

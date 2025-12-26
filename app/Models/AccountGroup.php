<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationship: Account Type
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    /**
     * Relationship: Accounts
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'asset_type', 'id');
    }

    /**
     * Get group by name
     */
    public static function getGroupByName($name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * Get account group by account ID
     */
    public static function getAccountGroupByAccountId($account_id)
    {
        $account = Account::find($account_id);
        if (!$account) {
            return null;
        }
        
        return self::find($account->asset_type);
    }

    /**
     * Get groups by account type
     */
    public static function getByAccountType($type_id)
    {
        return self::where('account_type_id', $type_id)->pluck('name', 'id');
    }
}

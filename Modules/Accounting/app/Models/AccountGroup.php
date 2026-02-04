<?php

namespace Modules\Accounting\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountGroup extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relationships
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'asset_type');
    }

    /**
     * Get group by name
     */
    public static function getGroupByName($group_name, $return_id = false)
    {
        $group = self::where('name', $group_name)->first();

        if ($return_id && $group) {
            return $group->id;
        }

        return $group;
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
     * Get groups by account type ID
     */
    public static function getGroupsByAccountTypeId($account_type_id)
    {
        return self::where('account_type_id', $account_type_id)
            ->pluck('name', 'id');
    }

    /**
     * Get groups for dropdown
     */
    public static function forDropdown()
    {
        return self::pluck('name', 'id');
    }
}


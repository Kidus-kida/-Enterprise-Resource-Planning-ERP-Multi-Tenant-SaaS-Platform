<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relationships
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'account_type_id');
    }

    public function accountGroups()
    {
        return $this->hasMany(AccountGroup::class, 'account_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(AccountType::class, 'parent_account_type_id');
    }

    /**
     * Get account type ID by name
     */
    public static function getAccountTypeIdByName($account_type_name, $business_id = null, $return_id = false)
    {
        if (!$business_id) {
            $business_id = request()->session()->get('user.business_id');
        }

        $type = self::where('business_id', $business_id)
            ->where('name', $account_type_name)
            ->first();

        if ($return_id && $type) {
            return $type->id;
        }

        return $type;
    }

    /**
     * Get account types for dropdown
     */
    public static function forDropdown($business_id = null)
    {
        if (!$business_id) {
            $business_id = request()->session()->get('user.business_id');
        }

        return self::where('business_id', $business_id)
            ->pluck('name', 'id');
    }
}

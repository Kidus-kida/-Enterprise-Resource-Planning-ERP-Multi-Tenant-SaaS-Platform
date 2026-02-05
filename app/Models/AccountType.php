<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends TenantModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationship: Accounts
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'account_type_id');
    }

    /**
     * Relationship: Parent Account Type
     */
    public function parentType()
    {
        return $this->belongsTo(AccountType::class, 'parent_account_type_id');
    }

    /**
     * Relationship: Sub Types
     */
    public function subTypes()
    {
        return $this->hasMany(AccountType::class, 'parent_account_type_id');
    }

    /**
     * Relationship: Account Groups
     */
    public function accountGroups()
    {
        return $this->hasMany(AccountGroup::class, 'account_type_id');
    }

    /**
     * Get account type ID by name
     */
    public static function getAccountTypeIdByName($name)
    {
        $type = self::where('name', $name)->first();
        return $type ? $type->id : null;
    }

    /**
     * Get for dropdown
     */
    public static function forDropdown($parent_only = false)
    {
        $query = self::query();
        
        if ($parent_only) {
            $query->whereNull('parent_account_type_id');
        }
        
        return $query->pluck('name', 'id');
    }
}


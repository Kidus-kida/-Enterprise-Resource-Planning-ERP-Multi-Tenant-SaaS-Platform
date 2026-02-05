<?php

namespace Modules\Contacts\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactGroup extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Return list of contact groups for a business
     *
     * @param  int $business_id
     * @param  bool  $prepend_none
     * @param  string|null $type
     * @return array
     */
    public static function forDropdown($business_id, $prepend_none = true, $include_all = false, $type = null)
    {
        $query = ContactGroup::where('business_id', $business_id);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $groups = $query->pluck('name', 'id');

        if ($prepend_none) {
            $groups->prepend(__('None'), '');
        }

        if ($include_all) {
            $groups->prepend(__('All'), '');
        }

        return $groups;
    }
}


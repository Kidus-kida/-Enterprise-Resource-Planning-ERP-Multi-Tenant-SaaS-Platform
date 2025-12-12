<?php

namespace Modules\Contacts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Return list of contact groups for dropdown
     *
     * @param  bool  $prepend_none
     * @param  string|null $type
     * @return array
     */
    public static function forDropdown($prepend_none = true, $type = null)
    {
        $query = ContactGroup::query();

        // If business logic expands, add business_id check here
        // $query->where('business_id', auth()->user()->business_id);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $groups = $query->pluck('name', 'id');

        if ($prepend_none) {
            $groups->prepend(__('None'), '');
        }

        return $groups;
    }
}

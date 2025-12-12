<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Contact extends Model
{
    use Notifiable;
    use SoftDeletes;

    protected $table = "contacts";

    protected $guarded = ['id'];

    protected $dates = ['deleted_at', 'contact_transaction_date'];

    protected $casts = [
        'sub_customers' => 'array',
        'notification_contacts' => 'array',
    ];

    // Constant from CommonConstants
    const GENERAL_CUSTOMER_GROUP = null; // Adjust as needed

    protected $attributes = [
        'customer_group_id' => self::GENERAL_CUSTOMER_GROUP
    ];

    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    public function scopeOnlySuppliers($query)
    {
        return $query->whereIn('contacts.type', ['supplier', 'both']);
    }

    public function scopeOnlyCustomers($query)
    {
        return $query->whereIn('contacts.type', ['customer', 'both']);
    }

    public function scopeOnlyActive($query)
    {
        return $query->where('contacts.active', 1);
    }

    // Removed relationships to non-existent models: loans, documentsAndnote, business

    /**
     * Return list of contact dropdown for a business
     *
     * @param $business_id int
     * @param $exclude_default = false (boolean)
     * @param $prepend_none = true (boolean)
     *
     * @return array users
     */
    public static function contactDropdown($business_id, $exclude_default = false, $prepend_none = true, $append_id = true)
    {
        $query = Contact::where('business_id', $business_id);
        if ($exclude_default) {
            $query->where('is_default', 0);
        }

        if ($append_id) {
            $query->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' - ', COALESCE(supplier_business_name, ''), '(', contact_id, ')')) AS supplier"),
                'id'
                    );
        } else {
            $query->select(
                'id',
                DB::raw("IF (supplier_business_name IS not null, CONCAT(name, ' (', supplier_business_name, ')'), name) as supplier")
            );
        }
        
        $contacts = $query->pluck('supplier', 'id');

        //Prepend none
        if ($prepend_none) {
            $contacts = $contacts->prepend('None', '');
        }

        return $contacts;
    }

    public static function customersDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['customer', 'both'])->onlyActive();

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' (', contact_id, ')')) AS customer"),
                'id'
                );
        } else {
            $all_contacts->select('id', DB::raw("name as customer"));
        }

        $customers = $all_contacts->pluck('customer', 'id');

        //Prepend none
        if ($prepend_none) {
            $customers = $customers->prepend('None', '');
        }

        return $customers;
    }

    public static function suppliersDropdown($business_id, $prepend_none = true, $append_id = true)
    {
        $all_contacts = Contact::where('business_id', $business_id)
                        ->whereIn('type', ['supplier', 'both']);

        if ($append_id) {
            $all_contacts->select(
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT(name, ' - ', COALESCE(supplier_business_name, ''), '(', contact_id, ')')) AS supplier"),
                'id'
                    );
        } else {
            $all_contacts->select(
                'id',
                DB::raw("CONCAT(name, ' (', supplier_business_name, ')') as supplier")
                );
        }

        $suppliers = $all_contacts->pluck('supplier', 'id');

        //Prepend none
        if ($prepend_none) {
            $suppliers = $suppliers->prepend('None', '');
        }

        return $suppliers;
    }

    public static function typeDropdown($prepend_all = false)
    {
        $types = [];

        if ($prepend_all) {
            $types[''] = 'All';
        }

        $types['customer'] = 'Customer';
        $types['supplier'] = 'Supplier';
        $types['both'] = 'Both (Supplier & Customer)';

        return $types;
    }
}

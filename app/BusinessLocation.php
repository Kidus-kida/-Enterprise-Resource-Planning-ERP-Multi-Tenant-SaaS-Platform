<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class BusinessLocation extends Model
{

    protected static $logAttributes = ['*'];

    protected static $logFillable = true;


    protected static $logName = 'Business Location';

    protected $fillable = ['business_id', 'company_id', 'location_id', 'name', 'landmark', 'country', 'state', 'city', 'zip_code', 'mobile', 'alternate_number', 'email', 'website', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'receipt_printer_type', 'printer_id', 'invoice_scheme_id', 'invoice_layout_id', 'selling_price_group_id', 'print_receipt_on_invoice', 'sale_invoice_layout_id', 'default_payment_accounts', 'featured_products', 'is_active', 'sale_invoice_layout_id', 'packing_slip_layout_id'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The company that owns the location.
     */
    public function company()
    {
        return $this->belongsTo(\App\Company::class);
    }

    /**
     * Return list of locations for a business
     *
     * @param int $business_id
     * @param boolean $show_all = false
     * @param array $receipt_printer_type_attribute =
     * @param boolean $append_id = true
     * @param boolean $check_permission = true
     *
     * @return \Illuminate\Support\Collection|array
     */
    public static function forDropdown($business_id, $show_all = false, $receipt_printer_type_attribute = false, $append_id = true, $check_permission = true)
    {
        $query = BusinessLocation::where('business_id', $business_id)->Active();

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            if (!auth()->user()->is_customer) {
                $query->whereIn('id', $permitted_locations);
            }
        }

        if ($append_id) {
            $query->select(
                DB::raw("IF(location_id IS NULL OR location_id='', name, CONCAT(name, ' (', location_id, ')')) AS name"),
                'id',
                'receipt_printer_type',
                'selling_price_group_id',
                'default_payment_accounts'
            );
        }

        $result = $query->get();

        $locations = $result->pluck('name', 'id');

        if ($show_all) {
            $locations->prepend(__('report.all_locations'), '');
        }

        if ($receipt_printer_type_attribute) {
            $attributes = collect($result)->mapWithKeys(function ($item) {
                return [
                    $item->id => [
                        'data-receipt_printer_type' => $item->receipt_printer_type,
                        'data-default_price_group' => $item->selling_price_group_id,
                        'data-default_payment_accounts' => $item->default_payment_accounts
                    ]
                ];
            })->all();

            return ['locations' => $locations, 'attributes' => $attributes];
        } else {
            return $locations;
        }
    }

    public static function getDropdownCollection($business_id, $show_all = false, $receipt_printer_type_attribute = false, $append_id = true)
    {
        $query = BusinessLocation::where('business_id', $business_id)->Active();

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            if (!auth()->user()->is_customer) {
                $query->whereIn('id', $permitted_locations);
            }
        }

        if ($append_id) {
            $query->select(
                DB::raw("IF(location_id IS NULL OR location_id='', name, CONCAT(name, ' (', location_id, ')')) AS name"),
                'id',
                'receipt_printer_type',
                'selling_price_group_id',
                'default_payment_accounts'
            );
        }

        $result = $query->get();

        $locations = $result/*->pluck('name', 'id')*/ ;

        if ($show_all) {
            $locations->prepend(__('report.all_locations'), '');
        }

        if ($receipt_printer_type_attribute) {
            $attributes = collect($result)->mapWithKeys(function ($item) {
                return [
                    $item->id => [
                        'data-receipt_printer_type' => $item->receipt_printer_type,
                        'data-default_price_group' => $item->selling_price_group_id,
                        'data-default_payment_accounts' => $item->default_payment_accounts
                    ]
                ];
            })->all();

            return ['locations' => $locations, 'attributes' => $attributes];
        } else {
            return $locations;
        }
    }

    public function price_group()
    {
        return $this->belongsTo(\App\SellingPriceGroup::class, 'selling_price_group_id');
    }

    /**
     * Scope a query to only include active location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }



    public function stores()
    {
        return $this->hasMany(\App\Store::class, 'location_id');
    }


    public static function getDefaultAccountIdForMethod($method_name, $location_id)
    {
        $business_id = request()->session()->get('business.id');
        $account_id = null;
        $defualt_accounts = BusinessLocation::where('business_id', $business_id)->where('id', $location_id)->first();
        if (!empty($defualt_accounts)) {
            $default_payment_accounts = (array) json_decode($defualt_accounts->default_payment_accounts);

            $account_id = $default_payment_accounts[$method_name]->account;
        }

        return $account_id;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fillable', 'some_other_attribute']);
    }


}

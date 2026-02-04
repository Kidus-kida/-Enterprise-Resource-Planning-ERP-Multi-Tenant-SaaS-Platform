<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transaction extends \App\Models\TenantModel
{
    // use LogsActivity;
    use SoftDeletes;
    use \App\Traits\HasCompany;

    // protected static $logAttributes = ['*'];

    // protected static $logFillable = true;

    // protected static $logName = 'Transaction';
    protected $dates = ['transaction_date'];

    protected $guarded = ['id'];

    protected $casts = [
        'is_pos' => 'boolean',
        'is_quotation' => 'boolean',
        'is_suspend' => 'boolean',
        'is_duplicate' => 'boolean',
        'is_direct_sale' => 'boolean',
        'is_customer_order' => 'boolean',
        'is_recurring' => 'boolean',
        'is_created_from_api' => 'boolean',
    ];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(['*']);
    // }

    public function purchase_lines()
    {
        return $this->hasMany(\App\PurchaseLine::class);
    }

    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax_id');
    }

    public function sales_person()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function transaction_store()
    {
        return $this->belongsTo(\App\Store::class, 'store_id', 'id');
    }

    public static function transactionTypes()
    {
        return [
            'sell' => __('sale.sale'),
            'purchase' => __('lang_v1.purchase'),
            'sell_return' => __('lang_v1.sell_return'),
            'purchase_return' => __('lang_v1.purchase_return'),
            'opening_balance' => __('lang_v1.opening_balance'),
            'payment' => __('lang_v1.payment'),
            'cheque_return' => __('lang_v1.cheque_return'),
            'sell_transfer' => __('lang_v1.sell_transfer'),
            'purchase_transfer' => __('lang_v1.purchase_transfer'),
        ];
    }

    /**
     * Shipping address custom method
     */
    public function shipping_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $shipping_address = [];

        if (!empty($addresses['shipping_address'])) {
            if (!empty($addresses['shipping_address']['shipping_name'])) {
                $shipping_address['name'] = $addresses['shipping_address']['shipping_name'];
            }
            if (!empty($addresses['shipping_address']['company'])) {
                $shipping_address['company'] = $addresses['shipping_address']['company'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_1'])) {
                $shipping_address['address_line_1'] = $addresses['shipping_address']['shipping_address_line_1'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_2'])) {
                $shipping_address['address_line_2'] = $addresses['shipping_address']['shipping_address_line_2'];
            }
            if (!empty($addresses['shipping_address']['shipping_city'])) {
                $shipping_address['city'] = $addresses['shipping_address']['shipping_city'];
            }
            if (!empty($addresses['shipping_address']['shipping_state'])) {
                $shipping_address['state'] = $addresses['shipping_address']['shipping_state'];
            }
            if (!empty($addresses['shipping_address']['shipping_country'])) {
                $shipping_address['country'] = $addresses['shipping_address']['shipping_country'];
            }
            if (!empty($addresses['shipping_address']['shipping_zip_code'])) {
                $shipping_address['zipcode'] = $addresses['shipping_address']['shipping_zip_code'];
            }
        }

        if ($array) {
            return $shipping_address;
        } else {
            return implode(', ', $shipping_address);
        }
    }

    /**
     * billing address custom method
     */
    public function billing_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $billing_address = [];

        if (!empty($addresses['billing_address'])) {
            if (!empty($addresses['billing_address']['billing_name'])) {
                $billing_address['name'] = $addresses['billing_address']['billing_name'];
            }
            if (!empty($addresses['billing_address']['company'])) {
                $billing_address['company'] = $addresses['billing_address']['company'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_1'])) {
                $billing_address['address_line_1'] = $addresses['billing_address']['billing_address_line_1'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_2'])) {
                $billing_address['address_line_2'] = $addresses['billing_address']['billing_address_line_2'];
            }
            if (!empty($addresses['billing_address']['billing_city'])) {
                $billing_address['city'] = $addresses['billing_address']['billing_city'];
            }
            if (!empty($addresses['billing_address']['billing_state'])) {
                $billing_address['state'] = $addresses['billing_address']['billing_state'];
            }
            if (!empty($addresses['billing_address']['billing_country'])) {
                $billing_address['country'] = $addresses['billing_address']['billing_country'];
            }
            if (!empty($addresses['billing_address']['billing_zip_code'])) {
                $billing_address['zipcode'] = $addresses['billing_address']['billing_zip_code'];
            }
        }

        if ($array) {
            return $billing_address;
        } else {
            return implode(', ', $billing_address);
        }
    }
}

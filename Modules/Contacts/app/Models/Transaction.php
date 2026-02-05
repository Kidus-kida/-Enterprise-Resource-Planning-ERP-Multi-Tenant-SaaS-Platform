<?php

namespace Modules\Contacts\Models;

use App\Models\TenantModel;

use Modules\Contacts\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    // Dates casting
    protected $casts = [
        'transaction_date' => 'datetime',
        'is_settlement' => 'boolean',
        'is_duplicate' => 'boolean',
        'is_direct_sale' => 'boolean',
        'is_quotation' => 'boolean',
        'is_customer_order' => 'boolean',
        'is_suspend' => 'boolean',
        'is_recurring' => 'boolean',
        'is_created_from_api' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function purchase_lines()
    {
        return $this->hasMany(\Modules\Purchase\Models\PurchaseLine::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(\Modules\Contacts\Models\TransactionPayment::class, 'transaction_id');
    }

    public function payment_lines()
    {
        return $this->hasMany(\Modules\Contacts\Models\TransactionPayment::class, 'transaction_id');
    }

    public function location()
    {
        return $this->belongsTo(\Modules\Contacts\Models\BusinessLocation::class, 'location_id');
    }

    public function return_parent()
    {
        return $this->belongsTo(Transaction::class, 'return_parent_id');
    }
    
    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    public function stock_adjustment_lines()
    {
        return $this->hasMany(\Modules\StockAdjustment\Models\StockAdjustmentLine::class, 'transaction_id');
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
    public function subscription_invoices()
    {
        return $this->hasMany(Transaction::class, 'recur_parent_id');
    }
}


<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Contacts\Models\Transaction;
use App\Models\Product;
use App\Models\Variation;

class PurchaseLine extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variations()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    public function sub_unit()
    {
        return $this->belongsTo(\App\Unit::class, 'sub_unit_id');
    }

    /**
     * Set the quantity.
     *
     * @param  string  $value
     * @return float $value
     */
    public function getQuantityAttribute($value)
    {
        return (float)$value;
    }

    /**
     * Give the quantity remaining for a particular
     * purchase line.
     *
     * @return float $value
     */
    public function getQuantityRemainingAttribute()
    {
        return (float)($this->quantity - $this->quantity_used);
    }
}

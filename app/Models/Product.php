<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends TenantModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'sub_unit_ids' => 'array',
    ];

    public function product_variations()
    {
        return $this->hasMany(\App\Models\Variation::class, 'product_id'); // Assuming Variation link
    }
    
    // Alias for simple variations
    public function variations()
    {
        return $this->hasMany(\App\Models\Variation::class);
    }

    public function purchase_lines()
    {
        return $this->hasMany(\Modules\Purchase\Models\PurchaseLine::class);
    }

        /**
     * Get the brand associated with the product.
     */
    public function brand()
    {
        return $this->belongsTo(\App\Brands::class);
    }
    
    public function getLotNumbers()
    {
        return $this->belongsTo(\Modules\Manufacturing\Entities\MfgSettings::class, 'id', 'id_product');
    }

    /**
     * Get the unit associated with the product.
     */
    public function unit()
    {
        return $this->belongsTo(\App\Unit::class);
    }

    /**
     * Get the unit associated with the product.
     */
    public function second_unit()
    {
        return $this->belongsTo(\App\Unit::class, 'secondary_unit_id');
    }

    /**
     * Get category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(\App\Category::class);
    }

    /**
     * Get sub-category associated with the product.
     */
    public function sub_category()
    {
        return $this->belongsTo(\App\Category::class, 'sub_category_id', 'id');
    }

    /**
     * Get the tax associated with the product.
     */
    public function product_tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax', 'id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('products.is_inactive', 0);
    }
}


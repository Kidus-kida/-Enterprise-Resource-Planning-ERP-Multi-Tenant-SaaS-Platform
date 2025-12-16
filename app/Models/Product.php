<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
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
}

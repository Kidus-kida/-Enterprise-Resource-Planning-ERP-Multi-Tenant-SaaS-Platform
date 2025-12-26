<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    
    public static function forBusiness($business_id)
    {
        return self::where('business_id', $business_id)->get();
    }
}

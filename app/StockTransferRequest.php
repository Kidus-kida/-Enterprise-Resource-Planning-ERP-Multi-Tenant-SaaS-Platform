<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StockTransferRequest extends Model
{
    // use LogsActivity; 

    protected $guarded = ['id'];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(['*']);
    // }

    public function products()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }
}

<?php

namespace Modules\StockAdjustment\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentSetting extends TenantModel
{
    //
    protected static $logAttributes = ['*'];

    protected static $logFillable = true;


    protected static $logName = 'Account';

    protected $table = 'stock_adjustment_settings';
    
    protected $fillable = [
        'business_id',
        'date',
        'adjustment_type',
        'category_id',
        'sub_category_id',
        'account_to_link',
        'stock_group',
        'stock_account'
    ];
    
    protected $guarded = ['id'];
    
}


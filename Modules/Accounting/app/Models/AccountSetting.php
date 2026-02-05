<?php

namespace Modules\Accounting\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountSetting extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'business_id',
        'key',
        'settings',
        'created_by',
        'date',
        'account_id',
        'group_id',
        'amount',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}


<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'settings' => 'array',
    ];
}

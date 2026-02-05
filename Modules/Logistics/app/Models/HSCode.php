<?php

namespace Modules\Logistics\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class HSCode extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'hs_codes';

    protected $fillable = [
        'code',
        'description',
        'tariff_rate',
        'excise_rate',
        'vat_rate',
        'surtax_rate',
        'withholding_rate',
        'is_active',
    ];

    protected $casts = [
        'tariff_rate' => 'decimal:2',
        'excise_rate' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'surtax_rate' => 'decimal:2',
        'withholding_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}


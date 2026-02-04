<?php

namespace Modules\Logistics\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomsDeclaration extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'declaration_no',
        'hs_code_id',
        'tariff_rate',
        'cif_value_usd',
        'exchange_rate',
        'cif_value_etb',
        'import_duty',
        'vat',
        'surtax',
        'excise',
        'withholding',
        'customs_service_fee',
        'total_duties',
        'risk_channel',
        'declaration_date',
        'clearance_date',
        'status',
        'progress',
        'rejection_reason',
    ];

    protected $casts = [
        'tariff_rate' => 'decimal:2',
        'cif_value_usd' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'cif_value_etb' => 'decimal:2',
        'import_duty' => 'decimal:2',
        'vat' => 'decimal:2',
        'surtax' => 'decimal:2',
        'excise' => 'decimal:2',
        'withholding' => 'decimal:2',
        'customs_service_fee' => 'decimal:2',
        'total_duties' => 'decimal:2',
        'declaration_date' => 'date',
        'clearance_date' => 'date',
        'progress' => 'integer',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function hsCode()
    {
        return $this->belongsTo(HSCode::class);
    }
}


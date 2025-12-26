<?php

namespace Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_no',
        'po_reference',
        'vendor',
        'vendor_country',
        'incoterms',
        'port_of_loading',
        'port_of_discharge',
        'transport_mode',
        'status',
        'expected_arrival',
        'actual_arrival',
        'dry_port_id',
        'user_id',
        'value_etb',
    ];

    protected $casts = [
        'expected_arrival' => 'date',
        'actual_arrival' => 'date',
    ];

    public function dryPort()
    {
        return $this->belongsTo(DryPort::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function containers()
    {
        return $this->hasMany(Container::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function customsDeclaration()
    {
        return $this->hasOne(CustomsDeclaration::class);
    }
}

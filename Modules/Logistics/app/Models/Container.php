<?php

namespace Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'container_no',
        'seal_no',
        'size',
        'type',
        'shipping_line',
        'status',
        'demurrage_days',
        'arrived_at_djibouti',
        'arrived_at_dry_port',
        'location',
    ];

    protected $casts = [
        'demurrage_days' => 'integer',
        'arrived_at_djibouti' => 'datetime',
        'arrived_at_dry_port' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function transportTrips()
    {
        return $this->hasMany(TransportTrip::class);
    }
}

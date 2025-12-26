<?php

namespace Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportTrip extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transport_trips';

    protected $fillable = [
        'trip_no',
        'container_id',
        'vehicle_plate',
        'driver_name',
        'driver_phone',
        'origin',
        'destination',
        'distance_km',
        'status',
        'progress',
        'departed_at',
        'eta',
        'completed_at',
    ];

    protected $casts = [
        'distance_km' => 'integer',
        'departed_at' => 'datetime',
        'eta' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }
}

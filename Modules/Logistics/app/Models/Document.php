<?php

namespace Modules\Logistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'name',
        'type',
        'file_path',
        'file_size',
        'uploaded_by',
        'uploaded_at',
        'status',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'expiry_date' => 'date',
        'file_size' => 'integer',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

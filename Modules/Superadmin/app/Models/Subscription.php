<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Superadmin\Database\Factories\SubscriptionFactory;
use App\Business;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'business_id',
        'package_id',
        'subscribed_user_count',
        'start_date',
        'end_date',
        'package_details',
        'module_activation_details',
        'paid_via',
        'payment_transaction_id',
        'status',
        'created_id',
        'base_price',
        'addons_price',
        'total_price',
        'company_count'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'package_details' => 'array',
        'company_count' => 'integer',
        'created_at' => 'datetime',
        'module_activation_details' => 'array',
        'subscribed_user_count' => 'integer',
        'base_price' => 'decimal:4',
        'addons_price' => 'decimal:4',
        'total_price' => 'decimal:4'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function manualPayments()
    {
        return $this->hasMany(ManualPayment::class);
    }

    public function addons()
    {
        return $this->belongsToMany(PackageAddon::class, 'subscription_addons')
                    ->withPivot('price_at_time')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                     ->where('end_date', '>=', Carbon::now());
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function isExpired()
    {
        return $this->end_date && Carbon::parse($this->end_date)->isPast();
    }

    public function isActive()
    {
        return $this->status === 'approved' && !$this->isExpired();
    }

    protected static function newFactory(): SubscriptionFactory
    {
        //return SubscriptionFactory::new();
    }
}

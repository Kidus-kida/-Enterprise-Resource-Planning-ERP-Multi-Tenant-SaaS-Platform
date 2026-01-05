<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Superadmin\Database\Factories\PackageFactory;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency_id',
        'interval',
        'interval_count',
        'trial_days',
        'location_count',
        'user_count',
        'product_count',
        'invoice_count',
        'custom_permissions',
        'is_active',
        'is_private',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'custom_permissions' => 'array',
        'is_active' => 'boolean',
        'is_private' => 'boolean',
        'location_count' => 'integer',
        'user_count' => 'integer',
        'product_count' => 'integer',
        'invoice_count' => 'integer',
        'trial_days' => 'integer',
        'interval_count' => 'integer',
        'sort_order' => 'integer'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', 0);
    }

    protected static function newFactory(): PackageFactory
    {
        //return PackageFactory::new();
    }
}

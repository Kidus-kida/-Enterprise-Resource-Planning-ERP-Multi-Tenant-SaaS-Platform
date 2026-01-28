<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Superadmin\Database\Factories\PackageFactory;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_per_user',
        'currency_id',
        'interval',
        'interval_count',
        'trial_days',
        'location_count',
        'company_count',
        'enable_multi_company',
        'user_count',
        'min_users',
        'is_per_user_pricing',
        'product_count',
        'invoice_count',
        'custom_permissions',
        'is_active',
        'is_private',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'price_per_user' => 'decimal:4',
        'custom_permissions' => 'array',
        'is_active' => 'boolean',
        'is_private' => 'boolean',
        'is_per_user_pricing' => 'boolean',
        'location_count' => 'integer',
        'company_count' => 'integer',
        'enable_multi_company' => 'boolean',
        'user_count' => 'integer',
        'min_users' => 'integer',
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

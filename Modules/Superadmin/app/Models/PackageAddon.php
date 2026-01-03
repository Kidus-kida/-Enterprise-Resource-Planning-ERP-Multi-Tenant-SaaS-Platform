<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Superadmin\Database\Factories\PackageAddonFactory;

class PackageAddon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'name',
        'description',
        'price',
        'module_key',
        'features',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationship: Add-on belongs to Module
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_addons')
                    ->withPivot('price_at_time')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    protected static function newFactory(): PackageAddonFactory
    {
        //return PackageAddonFactory::new();
    }
}

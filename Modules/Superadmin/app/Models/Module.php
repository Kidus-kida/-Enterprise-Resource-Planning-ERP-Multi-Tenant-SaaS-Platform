<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Superadmin\Database\Factories\ModuleFactory;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'key',
        'icon',
        'routes',
        'permissions',
        'description',
        'is_core',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'routes' => 'array',
        'permissions' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationship: Module has many Add-ons
    public function addons()
    {
        return $this->hasMany(PackageAddon::class);
    }

    // Scope: Active modules
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Scope: Core modules
    public function scopeCore($query)
    {
        return $query->where('is_core', 1);
    }

    // Scope: Optional modules
    public function scopeOptional($query)
    {
        return $query->where('is_core', 0);
    }

    protected static function newFactory(): ModuleFactory
    {
        //return ModuleFactory::new();
    }
}

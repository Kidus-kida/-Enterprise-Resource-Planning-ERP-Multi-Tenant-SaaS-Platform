<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    /**
     * Get the database connection for the model.
     * Dynamically uses 'tenant' connection when configured, otherwise uses default.
     *
     * @return string
     */
    public function getConnectionName()
    {
        if (!empty(config('database.connections.tenant'))) {
            return 'tenant';
        }
        return config('database.default');
    }


    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function forDropdown($business_id, $prepend_shared = false)
    {
        $companies = self::where('business_id', $business_id)->where('is_active', 1)->pluck('name', 'id');
        
        if ($prepend_shared) {
            $companies->prepend('All Companies', '');
        }
        
        return $companies;
    }

    /**
     * The business that owns the company.
     */
    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    /**
     * The locations (branches) that belong to the company.
     */
    public function business_locations()
    {
        return $this->hasMany('App\BusinessLocation');
    }
}

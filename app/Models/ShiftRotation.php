<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRotation extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'frequency_type',
        'frequency_interval',
        'start_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
        'frequency_interval' => 'integer',
    ];

    /**
     * Get the steps for this rotation.
     */
    public function steps()
    {
        return $this->hasMany(ShiftRotationStep::class)->orderBy('step_order');
    }


    /**
     * Get the company that owns the rotation.
     */
    public function company()
    {
        return $this->belongsTo(\App\Company::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRotationStep extends Model
{
    protected $fillable = [
        'shift_rotation_id',
        'shift_id',
        'step_order',
    ];

    /**
     * Get the rotation this step belongs to.
     */
    public function rotation()
    {
        return $this->belongsTo(ShiftRotation::class, 'shift_rotation_id');
    }

    /**
     * Get the shift associated with this step.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}

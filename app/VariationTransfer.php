<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariationTransfer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'variation_transfers';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the business that owns the variation transfer.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the user who created the variation transfer.
     */
    public function created_by()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}

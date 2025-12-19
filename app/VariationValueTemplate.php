<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariationValueTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'variation_template_id'];

    public function variationTemplate()
    {
        return $this->belongsTo(\App\VariationTemplate::class);
    }
}

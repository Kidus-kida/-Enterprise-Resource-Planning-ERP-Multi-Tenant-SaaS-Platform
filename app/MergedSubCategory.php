<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MergedSubCategory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'merged_sub_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id',
        'date_and_time',
        'category_id',
        'merged_sub_category_name',
        'sub_categories',
        'status',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_and_time' => 'date',
        'status' => 'integer',
    ];
}

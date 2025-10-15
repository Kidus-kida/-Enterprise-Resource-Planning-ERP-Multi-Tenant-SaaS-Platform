<?php

namespace Modules\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Crm\Database\Factories\BudgetCategoryFactory;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected static function newFactory(): BudgetCategoryFactory
    {
        return BudgetCategoryFactory::new();
    }
}



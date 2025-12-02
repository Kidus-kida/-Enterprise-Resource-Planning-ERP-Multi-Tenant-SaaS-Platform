<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCalculation extends Model
{
    //
     protected $fillable = [
        'salary_from',
        'salary_to',
        'percentage',
        'deducted_amount',
    ];
}

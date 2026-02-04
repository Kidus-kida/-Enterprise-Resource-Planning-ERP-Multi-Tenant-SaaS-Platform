<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCalculation extends TenantModel
{
    //
     protected $fillable = [
        'salary_from',
        'salary_to',
        'percentage',
        'deducted_amount',
    ];
}


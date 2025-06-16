<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'type_name',
        'max_date_allowed',
        'leave_allowed_interval',
        'description',
        
    ];


}

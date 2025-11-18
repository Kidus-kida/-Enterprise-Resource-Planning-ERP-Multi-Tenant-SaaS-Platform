<?php

namespace Modules\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'status',
        'source',
        'notes',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Modules\Crm\Database\Factories\LeadFactory::new();
    }
}
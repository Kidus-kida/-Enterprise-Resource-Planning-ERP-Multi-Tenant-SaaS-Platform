<?php

namespace Modules\Contacts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerStatementDetail extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
     protected $casts = [
        'date' => 'date',
        'order_date' => 'date',
    ];

    public function statement()
    {
        return $this->belongsTo(CustomerStatement::class, 'statement_id');
    }
}

<?php

namespace Modules\Contacts\Models;

use Modules\Contacts\Models\Contact;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerStatement extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'print_date' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(CustomerStatementDetail::class, 'statement_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

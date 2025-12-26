<?php

namespace Modules\Contacts\Models;

use Modules\Contacts\Models\Contact;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'paid_on' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
    
    public function contact()
    {
        // Sometimes payment is directly linked to a contact (like an advance) 
        // or through the transaction
        return $this->belongsTo(Contact::class, 'payment_for'); 
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

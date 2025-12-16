<?php

namespace Modules\Contacts\Models;

use App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    // Dates casting
    protected $casts = [
        'transaction_date' => 'datetime',
        'is_settlement' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function business()
    {
        // return $this->belongsTo(Business::class);
        // Assuming Business model implementation comes later or uses User business_id
    }

    public function purchase_lines()
    {
        return $this->hasMany(\Modules\Purchase\Models\PurchaseLine::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(\Modules\Contacts\Models\TransactionPayment::class, 'transaction_id');
    }
}

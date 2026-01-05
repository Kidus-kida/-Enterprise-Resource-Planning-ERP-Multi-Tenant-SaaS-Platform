<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Superadmin\Database\Factories\ManualPaymentFactory;
use App\Business;
use App\Models\User;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'subscription_id',
        'amount',
        'currency',
        'payment_method',
        'reference_number',
        'receipt_path',
        'status',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'approved_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // protected static function newFactory(): ManualPaymentFactory
    // {
    //     return ManualPaymentFactory::new();
    // }
}

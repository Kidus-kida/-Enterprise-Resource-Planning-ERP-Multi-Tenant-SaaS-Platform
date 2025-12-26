<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Contacts\Models\TransactionPayment;

class TransactionPaymentUpdated
{
    use SerializesModels;

    public $payment;
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TransactionPayment $payment, $transaction)
    {
        $this->payment = $payment;
        $this->transaction = $transaction;
    }
}

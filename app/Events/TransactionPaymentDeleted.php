<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class TransactionPaymentDeleted
{
    use SerializesModels;

    public $paymentId;
    public $accountId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($paymentId, $accountId)
    {
        $this->paymentId = $paymentId;
        $this->accountId = $accountId;
    }
}

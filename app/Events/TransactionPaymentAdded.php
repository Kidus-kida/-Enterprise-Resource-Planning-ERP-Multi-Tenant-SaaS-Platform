<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Contacts\Models\TransactionPayment;

class TransactionPaymentAdded
{
    use SerializesModels;

    public $payment;
    public $formInput;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TransactionPayment $payment, $formInput)
    {
        $this->payment = $payment;
        $this->formInput = $formInput;
    }
}

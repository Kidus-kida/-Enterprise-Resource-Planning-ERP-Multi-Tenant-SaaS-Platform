<?php

namespace Modules\Accounting\Listeners;

use App\Events\TransactionPaymentAdded;
use Modules\Accounting\Models\AccountTransaction;

class MapTransactionPaymentToAccountTransaction
{
    public function handle(TransactionPaymentAdded $event)
    {
        $payment = $event->payment;
        $formInput = $event->formInput;

        if (!empty($formInput['account_id'])) {
            $at_data = [
                'amount' => $payment->amount,
                'account_id' => $formInput['account_id'],
                'type' => 'credit', // Default for purchase payments
                'operation_date' => $payment->paid_on,
                'created_by' => $payment->created_by,
                'transaction_id' => $payment->transaction_id,
                'transaction_payment_id' => $payment->id,
                'business_id' => $payment->business_id,
            ];

            // Determine type based on transaction type if available
            if (isset($formInput['transaction_type'])) {
                if ($formInput['transaction_type'] == 'sell') {
                    $at_data['type'] = 'debit';
                }
            }

            AccountTransaction::createAccountTransaction($at_data);
        }
    }
}

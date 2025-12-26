<?php

namespace Modules\Accounting\Listeners;

use App\Events\TransactionPaymentDeleted;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountTransaction;

class HandleTransactionPaymentDeleted
{
    public function handle(TransactionPaymentDeleted $event)
    {
        $paymentId = $event->paymentId;
        $accountId = $event->accountId;

        if (!empty($accountId)) {
            // Find the account transaction that was deleted
            $accountTransactions = AccountTransaction::withTrashed()
                ->where('transaction_payment_id', $paymentId)
                ->get();

            // Restore the account balance for each deleted account transaction
            foreach ($accountTransactions as $accountTransaction) {
                $account = Account::find($accountTransaction->account_id);
                
                if ($account) {
                    // Reverse the balance change
                    if ($accountTransaction->type == 'credit') {
                        // If it was a credit (money out), restore by adding back
                        $account->current_balance += $accountTransaction->amount;
                    } else {
                        // If it was a debit (money in), restore by subtracting
                        $account->current_balance -= $accountTransaction->amount;
                    }
                    $account->save();
                }
            }
        }
    }
}

<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Contacts\Models\Transaction;
use Modules\Contacts\Models\TransactionPayment;
use App\Utils\Util;

class TransactionUtil extends Util
{
    /**
     * Create or update payment lines - Full ERP logic
     */
    public function createOrUpdatePaymentLines($transaction, $payments, $business_id = null, $user_id = null, $from_upload = false, $status = null)
    {
        $payments_formatted = [];
        $edit_ids = [0];
        
        if (!is_object($transaction)) {
            $transaction = Transaction::find($transaction);
        }

        // If status is not passed, get it from transaction
        if (empty($status)) {
            $status = $transaction->status;
        }

        if (empty($business_id)) {
            $business_id = $transaction->business_id;
        }

        if (empty($user_id)) {
            $user_id = Auth::user()->id ?? 1;
        }

        foreach ($payments as $payment) {
            // Check if amount is present
            if (!isset($payment['amount']) || empty($payment['amount']) || $payment['amount'] <= 0) {
                continue;
            }

            $payment_data = [
                'transaction_id' => $transaction->id,
                'amount' => $this->num_uf($payment['amount']),
                'method' => $payment['method'] ?? 'cash',
                'business_id' => $business_id,
                'is_return' => isset($payment['is_return']) ? $payment['is_return'] : 0,
                'card_transaction_number' => $payment['card_transaction_number'] ?? null,
                'card_number' => $payment['card_number'] ?? null,
                'card_type' => $payment['card_type'] ?? null,
                'card_holder_name' => $payment['card_holder_name'] ?? null,
                'card_month' => $payment['card_month'] ?? null,
                'card_year' => $payment['card_year'] ?? null,
                'card_security' => $payment['card_security'] ?? null,
                'cheque_number' => $payment['cheque_number'] ?? null,
                'bank_account_number' => $payment['bank_account_number'] ?? null,
                'note' => $payment['note'] ?? null,
                'paid_on' => isset($payment['paid_on']) ? $this->uf_date($payment['paid_on'], true) : \Carbon\Carbon::now()->toDateTimeString(),
                'created_by' => $user_id,
                'payment_for' => $transaction->contact_id,
                'payment_ref_no' => $payment['payment_ref_no'] ?? null,
                'account_id' => $payment['account_id'] ?? null
            ];
            
            // Update existing payment
            if (isset($payment['payment_id'])) {
                $payment_obj = TransactionPayment::findOrFail($payment['payment_id']);
                $payment_obj->update($payment_data);
                $edit_ids[] = $payment['payment_id'];
            } else {
                // Create new payment
                $tp = TransactionPayment::create($payment_data);
                $edit_ids[] = $tp->id;
            }
        }
        
        // Delete removed payments
        TransactionPayment::where('transaction_id', $transaction->id)
                ->whereNotIn('id', $edit_ids)
                ->delete();
        
        return true;
    }

    /**
     * Update payment status of purchase/sale
     */
    public function updatePaymentStatus($transaction_id, $final_amount = null)
    {
        $status = $this->calculatePaymentStatus($transaction_id, $final_amount);
        $transaction = Transaction::find($transaction_id);
        
        if ($transaction) {
            $transaction->payment_status = $status;
            $transaction->save();
        }
        
        return $status;
    }

    /**
     * Calculate payment status
     */
    public function calculatePaymentStatus($transaction_id, $final_amount = null)
    {
        $total_paid = $this->getTotalPaid($transaction_id);
        
        if (is_null($final_amount)) {
            $transaction = Transaction::find($transaction_id);
            if ($transaction) {
                $final_amount = $transaction->final_total;
            } else {
                return 'due';
            }
        }

        $status = 'due';
        if ($final_amount <= $total_paid) {
            $status = 'paid';
        } elseif ($total_paid > 0 && $final_amount > $total_paid) {
            $status = 'partial';
        }
        
        return $status;
    }

    /**
     * Get total amount paid for a transaction
     */
    public function getTotalPaid($transaction_id)
    {
        $total_paid = TransactionPayment::where('transaction_id', $transaction_id)
            ->where('is_return', 0)
            ->sum('amount');

        return $total_paid;
    }
    
    /**
     * Calculate and update VAT for transaction
     */
    public function calculateAndUpdateVAT($transaction)
    {
        // Simplified VAT calculation
        // Full implementation would calculate complex VAT based on business rules
        
        if ($transaction->is_vat == 1 && !empty($transaction->tax_id)) {
            $total_before_tax = 0;
            $total_tax = 0;
            
            foreach ($transaction->purchase_lines as $line) {
                $line_total = $line->quantity * $line->purchase_price;
                $total_before_tax += $line_total;
                $total_tax += $line->quantity * $line->item_tax;
            }
            
            $transaction->total_before_tax = $total_before_tax;
            $transaction->tax_amount = $total_tax;
            $transaction->final_total = $total_before_tax + $total_tax;
            $transaction->save();
        }
        
        return true;
    }

    /**
     * Get purchase currency details
     */
    public function purchaseCurrencyDetails($business_id)
    {
        // Placeholder returning basic details until Currency table is available
        // In full implementation, this would fetch from business currency settings
        return (object)[
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'symbol' => 'Br',
            'code' => 'ETB'
        ];
    }

    /**
     * Manage stock account entries
     */
    public function manageStockAccount($transaction, $lines, $type, $total, $account_id = null, $status = null)
    {
        // Placeholder for accounting integration
        // Would create AccountTransaction records
        return true;
    }
}

<?php

namespace Modules\Superadmin\Services;

use Modules\Superadmin\Models\ManualPayment;
use Modules\Superadmin\Models\Subscription;
use App\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManualPaymentService
{
    public function uploadReceipt($file, string $businessId)
    {
        $path = $file->store('receipts/' . $businessId, 'public');
        return $path;
    }

    public function createPaymentRecord(Business $business, array $data)
    {
        $paymentData = [
            'business_id' => $business->id,
            'subscription_id' => $data['subscription_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'ETB',
            'payment_method' => $data['payment_method'] ?? 'Bank Transfer',
            'reference_number' => $data['reference_number'] ?? null,
            'receipt_path' => $data['receipt_path'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending'
        ];

        return ManualPayment::create($paymentData);
    }

    public function approvePayment(ManualPayment $payment, Subscription $subscription = null)
    {
        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => Carbon::now()
            ]);

            if ($subscription) {
                $payment->update(['subscription_id' => $subscription->id]);
                
                $subscriptionService = new SubscriptionService();
                $subscriptionService->approveSubscription($subscription);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectPayment(ManualPayment $payment, string $reason)
    {
        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now()
        ]);

        return true;
    }

    public function generateInvoice(ManualPayment $payment)
    {
        // Placeholder for invoice generation logic
        return [
            'payment_id' => $payment->id,
            'business' => $payment->business->name ?? 'N/A',
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'date' => $payment->created_at->format('Y-m-d'),
            'status' => $payment->status
        ];
    }

    public function getPendingPayments()
    {
        return ManualPayment::pending()
            ->with(['business', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPaymentHistory(Business $business)
    {
        return ManualPayment::where('business_id', $business->id)
            ->with(['subscription', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function deleteReceipt(ManualPayment $payment)
    {
        if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->update(['receipt_path' => null]);
        return true;
    }
}

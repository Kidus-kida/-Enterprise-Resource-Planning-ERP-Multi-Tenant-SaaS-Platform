@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Payment Details #{{ $payment->id }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.manual-payments.index') }}">Manual Payments</a></li>
                        <li class="breadcrumb-item active">#{{ $payment->id }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @if($payment->status == 'pending')
                        <span class="badge bg-warning p-2">
                            <i class="fa fa-clock-o"></i> Pending Approval
                        </span>
                    @elseif($payment->status == 'approved')
                        <span class="badge bg-success p-2">
                            <i class="fa fa-check-circle"></i> Approved
                        </span>
                    @else
                        <span class="badge bg-danger p-2">
                            <i class="fa fa-times-circle"></i> Rejected
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Payment Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Payment Information</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="30%">Payment ID:</th>
                                    <td>#{{ $payment->id }}</td>
                                </tr>
                                <tr>
                                    <th>Business:</th>
                                    <td>
                                        <a href="{{ route('superadmin.businesses.show', $payment->business_id) }}">
                                            {{ $payment->business->name ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Subscription:</th>
                                    <td>
                                        @if($payment->subscription)
                                            <a href="{{ route('superadmin.subscriptions.show', $payment->subscription_id) }}">
                                                #{{ $payment->subscription_id }}
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                Package: {{ $payment->subscription->package->name ?? 'N/A' }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><h5 class="text-success mb-0">{{ number_format($payment->amount, 2) }} ETB</h5></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><span class="badge bg-info">{{ ucfirst($payment->payment_method ?? 'Bank Transfer') }}</span></td>
                                </tr>
                                <tr>
                                    <th>Transaction Reference:</th>
                                    <td><code>{{ $payment->transaction_reference ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Submitted:</th>
                                    <td>{{ $payment->created_at->format('d M Y, h:i A') }} ({{ $payment->created_at->diffForHumans() }})</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($payment->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($payment->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Customer Notes -->
                        @if($payment->notes)
                            <div class="alert alert-info mt-3">
                                <h6><i class="fa fa-comment"></i> Customer Notes:</h6>
                                <p class="mb-0">{{ $payment->notes }}</p>
                            </div>
                        @endif

                        <!-- Admin Notes -->
                        @if($payment->admin_notes)
                            <div class="alert alert-secondary mt-3">
                                <h6><i class="fa fa-user-circle"></i> Admin Notes:</h6>
                                <p class="mb-0">{{ $payment->admin_notes }}</p>
                            </div>
                        @endif

                        <!-- Rejection Reason -->
                        @if($payment->status == 'rejected' && $payment->rejection_reason)
                            <div class="alert alert-danger mt-3">
                                <h6><i class="fa fa-exclamation-triangle"></i> Rejection Reason:</h6>
                                <p class="mb-0">{{ $payment->rejection_reason }}</p>
                            </div>
                        @endif

                        <!-- Receipt -->
                        @if($payment->receipt_path)
                            <div class="mt-4">
                                <h5>Receipt Attachment</h5>
                                <div class="border p-3 rounded">
                                    <i class="fa fa-file fa-3x text-primary mb-2"></i>
                                    <br>
                                    <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="btn btn-primary">
                                        <i class="fa fa-download"></i> View/Download Receipt
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action History -->
                @if($payment->approved_by || $payment->rejected_by)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Action History</h4>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <i class="fa fa-circle text-primary"></i>
                                    <div class="timeline-content">
                                        <h6>Payment Submitted</h6>
                                        <p class="text-muted mb-0">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                </div>

                                @if($payment->status == 'approved')
                                    <div class="timeline-item">
                                        <i class="fa fa-check-circle text-success"></i>
                                        <div class="timeline-content">
                                            <h6>Payment Approved</h6>
                                            <p class="text-muted mb-0">
                                                {{ $payment->updated_at->format('d M Y, h:i A') }}
                                                @if($payment->approved_by)
                                                    by User #{{ $payment->approved_by }}
                                                @endif
                                            </p>
                                            @if($payment->approved_amount)
                                                <p class="mb-0"><strong>Approved Amount:</strong> {{ number_format($payment->approved_amount, 2) }} ETB</p>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($payment->status == 'rejected')
                                    <div class="timeline-item">
                                        <i class="fa fa-times-circle text-danger"></i>
                                        <div class="timeline-content">
                                            <h6>Payment Rejected</h6>
                                            <p class="text-muted mb-0">
                                                {{ $payment->updated_at->format('d M Y, h:i A') }}
                                                @if($payment->rejected_by)
                                                    by User #{{ $payment->rejected_by }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Actions -->
            <div class="col-md-4">
                @if($payment->status == 'pending')
                    <!-- Approve Form -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fa fa-check"></i> Approve Payment</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('superadmin.manual-payments.approve', $payment->id) }}" method="POST">
                                @csrf
                                
                                <div class="form-group">
                                    <label>Approved Amount (Optional)</label>
                                    <input type="number" name="approved_amount" class="form-control" 
                                        placeholder="{{ number_format($payment->amount, 2) }}" step="0.01" min="0">
                                    <small class="text-muted">Leave empty to use submitted amount</small>
                                </div>

                                <div class="form-group">
                                    <label>Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" class="form-control" rows="3" 
                                        placeholder="Add internal notes about this approval..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-block" 
                                    onclick="return confirm('Are you sure you want to approve this payment?');">
                                    <i class="fa fa-check"></i> Approve Payment
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Reject Form -->
                    <div class="card border-danger mt-3">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fa fa-times"></i> Reject Payment</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('superadmin.manual-payments.reject', $payment->id) }}" method="POST">
                                @csrf
                                
                                <div class="form-group">
                                    <label>Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="4" 
                                        placeholder="Explain why this payment is being rejected..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger btn-block" 
                                    onclick="return confirm('Are you sure you want to reject this payment?');">
                                    <i class="fa fa-times"></i> Reject Payment
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Status Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Payment Status</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($payment->status == 'approved')
                                <i class="fa fa-check-circle fa-5x text-success mb-3"></i>
                                <h4 class="text-success">Approved</h4>
                                <p class="text-muted">This payment has been approved and processed.</p>
                            @else
                                <i class="fa fa-times-circle fa-5x text-danger mb-3"></i>
                                <h4 class="text-danger">Rejected</h4>
                                <p class="text-muted">This payment was rejected.</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Quick Links -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('superadmin.businesses.show', $payment->business_id) }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fa fa-building"></i> View Business
                        </a>
                        @if($payment->subscription)
                            <a href="{{ route('superadmin.subscriptions.show', $payment->subscription_id) }}" class="btn btn-outline-info btn-block mb-2">
                                <i class="fa fa-file-text"></i> View Subscription
                            </a>
                        @endif
                        <a href="{{ route('superadmin.manual-payments.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fa fa-arrow-left"></i> Back to Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item i {
    position: absolute;
    left: -30px;
    top: 5px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}
</style>
@endpush
@endsection

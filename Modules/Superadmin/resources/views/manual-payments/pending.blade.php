@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Pending Payment Approvals</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.manual-payments.index') }}">Manual Payments</a></li>
                        <li class="breadcrumb-item active">Pending</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.manual-payments.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to All Payments
                    </a>
                </div>
            </div>
        </div>

        @if($payments->count() > 0)
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> <strong>{{ $payments->total() }} payment(s)</strong> waiting for your review and approval.
            </div>
        @else
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <strong>All caught up!</strong> No pending payments at the moment.
            </div>
        @endif

        <!-- Pending Payments List -->
        <div class="row">
            @forelse($payments as $payment)
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-clock-o"></i> Payment #{{ $payment->id }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Business Info -->
                            <div class="mb-3">
                                <h6 class="text-muted">Business</h6>
                                <h5>
                                    <a href="{{ route('superadmin.businesses.show', $payment->business_id) }}">
                                        {{ $payment->business->name ?? 'N/A' }}
                                    </a>
                                </h5>
                            </div>

                            <!-- Payment Details -->
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="40%">Amount:</th>
                                    <td><strong class="text-success">{{ number_format($payment->amount, 2) }} ETB</strong></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><span class="badge bg-info">{{ ucfirst($payment->payment_method ?? 'Bank Transfer') }}</span></td>
                                </tr>
                                <tr>
                                    <th>Transaction Ref:</th>
                                    <td><code>{{ $payment->transaction_reference ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Submitted:</th>
                                    <td>{{ $payment->created_at->diffForHumans() }}</td>
                                </tr>
                            </table>

                            <!-- Subscription Info -->
                            @if($payment->subscription)
                                <div class="alert alert-info mt-2">
                                    <strong>For Subscription:</strong> 
                                    <a href="{{ route('superadmin.subscriptions.show', $payment->subscription_id) }}">
                                        #{{ $payment->subscription_id }}
                                    </a>
                                    <br>
                                    <small>Package: {{ $payment->subscription->package->name ?? 'N/A' }}</small>
                                </div>
                            @endif

                            <!-- Receipt -->
                            @if($payment->receipt_path)
                                <div class="mt-3">
                                    <h6>Receipt Attachment:</h6>
                                    <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-file"></i> View Receipt
                                    </a>
                                </div>
                            @endif

                            <!-- Notes -->
                            @if($payment->notes)
                                <div class="mt-3">
                                    <h6>Customer Notes:</h6>
                                    <p class="text-muted">{{ $payment->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('superadmin.manual-payments.show', $payment->id) }}" class="btn btn-info btn-block">
                                        <i class="fa fa-eye"></i> Review Details
                                    </a>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="badge bg-warning p-2">Awaiting Approval</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-check-circle fa-5x text-success mb-3"></i>
                            <h4>No Pending Payments</h4>
                            <p class="text-muted">All payments have been reviewed. Check back later!</p>
                            <a href="{{ route('superadmin.manual-payments.index') }}" class="btn btn-primary">
                                View All Payments
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
            <div class="row mt-3">
                <div class="col-md-12">
                    {{ $payments->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection

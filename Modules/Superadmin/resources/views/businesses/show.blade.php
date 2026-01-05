@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Business Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.businesses.index') }}">Businesses</a></li>
                        <li class="breadcrumb-item active">{{ $business->name }}</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('superadmin.businesses.edit', $business->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit Business
                    </a>
                    @if(!$business->tenant_id)
                        <a href="{{ route('superadmin.tenants.create', $business->id) }}" class="btn btn-success">
                            <i class="fa fa-server"></i> Create Tenant
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Business Details -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Business Information</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Business Name:</th>
                                    <td>{{ $business->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $business->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $business->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td>{{ $business->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Owner:</th>
                                    <td>
                                        {{ $business->owner->name ?? 'N/A' }}
                                        @if($business->owner)
                                            <br><small class="text-muted">{{ $business->owner->email }}</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($business->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $business->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $business->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <!-- Package Info -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Package & Tenant</h4>
                    </div>
                    <div class="card-body">
                        <h6>Current Package</h6>
                        @if($business->package)
                            <div class="alert alert-primary">
                                <h5>{{ $business->package->name }}</h5>
                                <p class="mb-1"><strong>Price:</strong> {{ number_format($business->package->price, 2) }} ETB</p>
                                <p class="mb-0"><strong>Billing:</strong> {{ $business->package->interval_count }} {{ ucfirst($business->package->interval) }}</p>
                            </div>
                        @else
                            <div class="alert alert-secondary">No package assigned</div>
                        @endif

                        <h6 class="mt-3">Tenant Status</h6>
                        @if($business->tenant_id)
                            <div class="alert alert-success">
                                <p class="mb-1"><strong>Status:</strong> Tenant Created</p>
                                <p class="mb-1"><strong>Tenant ID:</strong> {{ $business->tenant_id }}</p>
                                @if($business->subdomain)
                                    <p class="mb-0"><strong>Subdomain:</strong> {{ $business->subdomain }}</p>
                                @endif
                            </div>
                            @if($business->tenant)
                                <a href="{{ route('superadmin.tenants.setup-instructions', $business->tenant_id) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-info-circle"></i> View Setup Instructions
                                </a>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <p class="mb-2">Tenant not created yet</p>
                                <a href="{{ route('superadmin.tenants.create', $business->id) }}" class="btn btn-sm btn-success">
                                    <i class="fa fa-server"></i> Create Tenant Now
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Subscriptions ({{ $business->subscriptions->count() }})</h4>
                    </div>
                    <div class="card-body">
                        @if($business->subscriptions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($business->subscriptions as $subscription)
                                        <tr>
                                            <td>{{ $subscription->package->name ?? 'N/A' }}</td>
                                            <td>{{ $subscription->start_date ? $subscription->start_date->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                {{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}
                                                @if($subscription->end_date && $subscription->end_date->isPast())
                                                    <span class="badge bg-danger">Expired</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($subscription->status == 'waiting')
                                                    <span class="badge bg-warning">Waiting</span>
                                                @else
                                                    <span class="badge bg-danger">Declined</span>
                                                @endif
                                            </td>
                                            <td>{{ $subscription->created_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('superadmin.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No subscriptions yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Payments -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Payment History ({{ $business->manualPayments->count() }})</h4>
                    </div>
                    <div class="card-body">
                        @if($business->manualPayments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($business->manualPayments as $payment)
                                        <tr>
                                            <td>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                            <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($payment->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($payment->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->created_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('superadmin.payments.index') }}" class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No payment history.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

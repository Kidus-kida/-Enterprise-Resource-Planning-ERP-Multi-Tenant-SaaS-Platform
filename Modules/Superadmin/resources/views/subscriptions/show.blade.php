@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Subscription Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.subscriptions.index') }}">Subscriptions</a></li>
                        <li class="breadcrumb-item active">#{{ $subscription->id }}</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    @if($subscription->status == 'waiting')
                        <form action="{{ route('superadmin.subscriptions.approve', $subscription->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> Approve
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('superadmin.subscriptions.edit', $subscription->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscription Status Alert -->
        @if($subscription->status == 'waiting')
            <div class="alert alert-warning">
                <i class="fa fa-clock-o"></i> This subscription is <strong>waiting for approval</strong>. Use the approve button to activate it.
            </div>
        @elseif($subscription->isExpired())
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> This subscription has <strong>expired</strong>. Consider renewing it.
                <form action="{{ route('superadmin.subscriptions.renew', $subscription->id) }}" method="POST" style="display:inline;" class="float-end">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-refresh"></i> Renew Now
                    </button>
                </form>
            </div>
        @elseif($subscription->isActive())
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> This subscription is <strong>active</strong> and valid.
            </div>
        @endif

        <!-- Subscription Details -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Subscription Information</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Subscription ID:</th>
                                    <td>#{{ $subscription->id }}</td>
                                </tr>
                                <tr>
                                    <th>Business:</th>
                                    <td>
                                        <a href="{{ route('superadmin.businesses.show', $subscription->business_id) }}">
                                            {{ $subscription->business->name ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Package:</th>
                                    <td>
                                        <a href="{{ route('superadmin.packages.show', $subscription->package_id) }}">
                                            {{ $subscription->package->name ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Start Date:</th>
                                    <td>{{ $subscription->start_date ? $subscription->start_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>
                                        {{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}
                                        @if($subscription->end_date)
                                            <br><small class="text-muted">
                                                {{ $subscription->end_date->isPast() ? 'Expired ' . $subscription->end_date->diffForHumans() : 'Expires ' . $subscription->end_date->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($subscription->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($subscription->status == 'waiting')
                                            <span class="badge bg-warning">Waiting</span>
                                        @else
                                            <span class="badge bg-danger">Declined</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $subscription->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $subscription->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <!-- Package Snapshot -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Package Details (Snapshot)</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $packageDetails = $subscription->package_details ?? [];
                        @endphp
                        
                        @if(!empty($packageDetails))
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th>Base Price:</th>
                                        <td>{{ number_format($subscription->base_price ?? $packageDetails['price'] ?? 0, 2) }} ETB</td>
                                    </tr>
                                    <tr>
                                        <th>Add-ons Price:</th>
                                        <td>{{ number_format($subscription->addons_price ?? 0, 2) }} ETB</td>
                                    </tr>
                                    <tr>
                                        <th>Total Price:</th>
                                        <td><strong class="text-success">{{ number_format($subscription->total_price ?? 0, 2) }} ETB</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Billing Cycle:</th>
                                        <td>{{ ($packageDetails['interval_count'] ?? 1) }} {{ ucfirst($packageDetails['interval'] ?? 'months') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Locations:</th>
                                        <td>{{ ($packageDetails['location_count'] ?? 0) == 0 ? 'Unlimited' : $packageDetails['location_count'] }}</td>
                                    </tr>
                                    <tr>
                                        <th>Users:</th>
                                        <td>{{ ($packageDetails['user_count'] ?? 0) == 0 ? 'Unlimited' : $packageDetails['user_count'] }}</td>
                                    </tr>
                                    <tr>
                                        <th>Products:</th>
                                        <td>{{ ($packageDetails['product_count'] ?? 0) == 0 ? 'Unlimited' : $packageDetails['product_count'] }}</td>
                                    </tr>
                                    <tr>
                                        <th>Invoices:</th>
                                        <td>{{ ($packageDetails['invoice_count'] ?? 0) == 0 ? 'Unlimited' : $packageDetails['invoice_count'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No package details available</p>
                        @endif
                    </div>
                </div>

                <!-- Module Permissions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Module Permissions</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $permissions = $subscription->module_activation_details ?? [];
                        @endphp
                        
                        @if(!empty($permissions))
                            <div class="row">
                                @foreach($modules as $module)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            @if(isset($permissions[$module->key]) && $permissions[$module->key])
                                                <i class="fa fa-check-circle text-success me-2"></i>
                                            @else
                                                <i class="fa fa-times-circle text-danger me-2"></i>
                                            @endif
                                            <span class="ms-2">
                                                <i class="la {{ $module->icon ?? 'la-cube' }}"></i> {{ $module->name }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No module permissions defined</p>
                        @endif
                    </div>
                </div>

                <!-- Add-ons Section -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Selected Add-ons ({{ $subscription->addons->count() }})</h4>
                    </div>
                    <div class="card-body">
                        @if($subscription->addons && $subscription->addons->count() > 0)
                            <div class="list-group">
                                @foreach($subscription->addons as $addon)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $addon->name }}</h6>
                                                <small class="text-muted">{{ $addon->description }}</small>
                                                @if($addon->features)
                                                    <div class="mt-2">
                                                        @foreach($addon->features as $feature)
                                                            <span class="badge bg-light text-dark me-1 mb-1"><i class="fa fa-check text-success"></i> {{ $feature }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="badge bg-success">{{ number_format($addon->pivot->price_at_time, 0) }} ETB</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No add-ons selected for this subscription</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Payment History ({{ $subscription->manualPayments->count() }})</h4>
                    </div>
                    <div class="card-body">
                        @if($subscription->manualPayments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subscription->manualPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('d M Y') }}</td>
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
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No payment records for this subscription</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Superadmin Dashboard</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <!-- Businesses -->
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-building"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['total_businesses'] }}</h3>
                            <span>Total Businesses</span>
                        </div>
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['total_businesses'] > 0 ? ($stats['active_businesses'] / $stats['total_businesses']) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['active_businesses'] }} Active</small>
                    </div>
                </div>
            </div>

            <!-- Packages -->
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-cube"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['total_packages'] }}</h3>
                            <span>Total Packages</span>
                        </div>
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $stats['total_packages'] > 0 ? ($stats['active_packages'] / $stats['total_packages']) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['active_packages'] }} Active</small>
                    </div>
                </div>
            </div>

            <!-- Subscriptions -->
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-calendar-check-o"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['active_subscriptions'] }}</h3>
                            <span>Active Subscriptions</span>
                        </div>
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 70%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['waiting_subscriptions'] }} Waiting Approval</small>
                    </div>
                </div>
            </div>

            <!-- Tenants -->
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-server"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['total_tenants'] }}</h3>
                            <span>Tenants Created</span>
                        </div>
                        <div class="progress progress-sm mt-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $stats['total_businesses'] > 0 ? ($stats['total_tenants'] / $stats['total_businesses']) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $stats['pending_payments'] }} Pending Payments</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if($stats['waiting_subscriptions'] > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle"></i> You have <strong>{{ $stats['waiting_subscriptions'] }}</strong> subscription(s) waiting for approval.
                <a href="{{ route('superadmin.subscriptions.index') }}" class="alert-link">View Now</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($stats['pending_payments'] > 0)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa fa-money"></i> You have <strong>{{ $stats['pending_payments'] }}</strong> payment(s) pending approval.
                <a href="{{ route('superadmin.payments.pending') }}" class="alert-link">Review Payments</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($expiringSoon->count() > 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-clock-o"></i> <strong>{{ $expiringSoon->count() }}</strong> subscription(s) expiring within 7 days!
                <a href="#expiring-section" class="alert-link">View Below</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Recent Businesses -->
            <div class="col-md-6 d-flex">
                <div class="card card-table flex-fill">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Recent Businesses</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Business</th>
                                        <th>Package</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBusinesses as $business)
                                    <tr>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="{{ route('superadmin.businesses.show', $business->id) }}">{{ $business->name }}</a>
                                            </h2>
                                        </td>
                                        <td>
                                            @if($business->package)
                                                <span class="badge bg-primary">{{ $business->package->name }}</span>
                                            @else
                                                <span class="badge bg-secondary">No Package</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($business->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No businesses yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('superadmin.businesses.index') }}">View all businesses</a>
                    </div>
                </div>
            </div>

            <!-- Pending Subscriptions -->
            <div class="col-md-6 d-flex">
                <div class="card card-table flex-fill">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Pending Approvals</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Business</th>
                                        <th>Package</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingSubscriptions as $subscription)
                                    <tr>
                                        <td>{{ $subscription->business->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-primary">{{ $subscription->package->name ?? 'N/A' }}</span></td>
                                        <td>
                                            <a href="{{ route('superadmin.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-primary">Review</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No pending subscriptions</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('superadmin.subscriptions.index') }}">View all subscriptions</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        @if($expiringSoon->count() > 0)
        <div class="row" id="expiring-section">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Subscriptions Expiring Soon (Within 7 Days)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table">
                                <thead>
                                    <tr>
                                        <th>Business</th>
                                        <th>Package</th>
                                        <th>End Date</th>
                                        <th>Days Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringSoon as $subscription)
                                    <tr>
                                        <td>
                                            <a href="{{ route('superadmin.businesses.show', $subscription->business_id) }}">
                                                {{ $subscription->business->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-primary">{{ $subscription->package->name ?? 'N/A' }}</span></td>
                                        <td>{{ $subscription->end_date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $subscription->end_date->diffInDays(now()) }} days</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('superadmin.subscriptions.renew', $subscription->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Renew</button>
                                            </form>
                                            <a href="{{ route('superadmin.subscriptions.show', $subscription->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Recent Subscriptions</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table">
                                <thead>
                                    <tr>
                                        <th>Business</th>
                                        <th>Package</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <a href="{{ route('superadmin.businesses.show', $subscription->business_id) }}">
                                                {{ $subscription->business->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-primary">{{ $subscription->package->name ?? 'N/A' }}</span></td>
                                        <td>
                                            <small>
                                                {{ $subscription->start_date ? $subscription->start_date->format('d M Y') : 'N/A' }} - 
                                                {{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}
                                            </small>
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
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent subscriptions</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('superadmin.subscriptions.index') }}">View all subscriptions</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

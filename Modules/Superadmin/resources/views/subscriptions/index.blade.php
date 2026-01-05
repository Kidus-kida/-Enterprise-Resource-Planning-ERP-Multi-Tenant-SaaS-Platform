@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Subscription Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Subscriptions</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('superadmin.subscriptions.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> Create Subscription
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Subscriptions</h5>
                        <h2>{{ $subscriptions->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Approved</h5>
                        <h2 class="text-success">{{ $subscriptions->where('status', 'approved')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Waiting</h5>
                        <h2 class="text-warning">{{ $subscriptions->where('status', 'waiting')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Declined</h5>
                        <h2 class="text-danger">{{ $subscriptions->where('status', 'declined')->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Business</th>
                                <th>Package</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->id }}</td>
                                <td>
                                    <a href="{{ route('superadmin.businesses.show', $subscription->business_id) }}">
                                        {{ $subscription->business->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $subscription->package->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small>
                                        <strong>Start:</strong> {{ $subscription->start_date ? $subscription->start_date->format('d M Y') : 'N/A' }}<br>
                                        <strong>End:</strong> {{ $subscription->end_date ? $subscription->end_date->format('d M Y') : 'N/A' }}
                                        @if($subscription->end_date && $subscription->end_date->isPast())
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($subscription->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @if($subscription->isActive())
                                            <br><small class="text-success">Active</small>
                                        @elseif($subscription->isExpired())
                                            <br><small class="text-danger">Expired</small>
                                        @endif
                                    @elseif($subscription->status == 'waiting')
                                        <span class="badge bg-warning">Waiting</span>
                                    @else
                                        <span class="badge bg-danger">Declined</span>
                                    @endif
                                </td>
                                <td>{{ $subscription->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('superadmin.subscriptions.show', $subscription->id) }}">
                                                <i class="fa fa-eye m-r-5"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('superadmin.subscriptions.edit', $subscription->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            
                                            @if($subscription->status == 'waiting')
                                                <form action="{{ route('superadmin.subscriptions.approve', $subscription->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fa fa-check m-r-5"></i> Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('superadmin.subscriptions.decline', $subscription->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to decline this subscription?');">
                                                        <i class="fa fa-times m-r-5"></i> Decline
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($subscription->status == 'approved' && $subscription->isExpired())
                                                <form action="{{ route('superadmin.subscriptions.renew', $subscription->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fa fa-refresh m-r-5"></i> Renew
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($subscription->status != 'approved')
                                                <form action="{{ route('superadmin.subscriptions.destroy', $subscription->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subscription?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fa fa-trash-o m-r-5"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No subscriptions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

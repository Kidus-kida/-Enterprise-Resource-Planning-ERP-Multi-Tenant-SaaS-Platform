@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $addon->name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.addons.index') }}">Add-ons</a></li>
                        <li class="breadcrumb-item active">{{ $addon->name }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.addons.edit', $addon->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Add-on Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Add-on Details</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td><strong>{{ $addon->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Module:</th>
                                <td>
                                    @if($addon->module)
                                        <span class="badge bg-info">{{ $addon->module->name }}</span>
                                    @else
                                        <span class="text-muted">No module assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td><strong>{{ number_format($addon->price, 0) }} ETB</strong></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($addon->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Sort Order:</th>
                                <td>{{ $addon->sort_order }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $addon->description ?? 'No description' }}</td>
                            </tr>
                        </table>

                        @if($addon->features && count($addon->features) > 0)
                            <h5 class="mt-3">Features</h5>
                            <ul class="list-unstyled">
                                @foreach($addon->features as $feature)
                                    <li><i class="fa fa-check text-success"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h3>{{ $addon->subscriptions->count() }}</h3>
                                    <p class="text-muted">Active Subscriptions</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h3>{{ number_format($totalRevenue, 0) }} ETB</h3>
                                    <p class="text-muted">Total Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Actions</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.addons.toggle-active', $addon->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-{{ $addon->is_active ? 'warning' : 'success' }} btn-block">
                                <i class="fa fa-power-off"></i> {{ $addon->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form action="{{ route('superadmin.addons.destroy', $addon->id) }}" method="POST" 
                            onsubmit="return confirm('Are you sure you want to delete this add-on? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" 
                                {{ $addon->subscriptions->count() > 0 ? 'disabled' : '' }}>
                                <i class="fa fa-trash"></i> Delete Add-on
                            </button>
                        </form>
                        @if($addon->subscriptions->count() > 0)
                            <small class="text-muted">Cannot delete add-on with active subscriptions</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Linked Subscriptions -->
        @if($addon->subscriptions->count() > 0)
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Linked Subscriptions ({{ $addon->subscriptions->count() }})</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Business</th>
                                            <th>Package</th>
                                            <th>Price at Time</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($addon->subscriptions as $subscription)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('superadmin.businesses.show', $subscription->business_id) }}">
                                                        {{ $subscription->business->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $subscription->package->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($subscription->pivot->price_at_time, 0) }} ETB</td>
                                                <td>
                                                    <span class="badge bg-{{ $subscription->status == 'approved' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($subscription->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $subscription->start_date->format('d M Y') }}</td>
                                                <td>{{ $subscription->end_date->format('d M Y') }}</td>
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

    </div>
@endsection

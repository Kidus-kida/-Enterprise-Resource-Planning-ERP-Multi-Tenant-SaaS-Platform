@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Business Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Businesses</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('superadmin.businesses.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> Add Business
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
                        <h5 class="card-title">Total Businesses</h5>
                        <h2>{{ $businesses->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Active Businesses</h5>
                        <h2 class="text-success">{{ $businesses->where('is_active', 1)->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">With Tenants</h5>
                        <h2 class="text-info">{{ $businesses->whereNotNull('tenant_id')->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Inactive</h5>
                        <h2 class="text-danger">{{ $businesses->where('is_active', 0)->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Businesses Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Business Name</th>
                                <th>Contact</th>
                                <th>Package</th>
                                <th>Tenant Status</th>
                                <th>Subscriptions</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($businesses as $business)
                            <tr>
                                <td>{{ $business->id }}</td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('superadmin.businesses.show', $business->id) }}">
                                            {{ $business->name }}
                                        </a>
                                    </h2>
                                </td>
                                <td>
                                    @if($business->email)
                                        <small><i class="fa fa-envelope"></i> {{ $business->email }}</small><br>
                                    @endif
                                    @if($business->phone)
                                        <small><i class="fa fa-phone"></i> {{ $business->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($business->package)
                                        <span class="badge bg-primary">{{ $business->package->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">No Package</span>
                                    @endif
                                </td>
                                <td>
                                    @if($business->tenant_id)
                                        <span class="badge bg-success">
                                            <i class="fa fa-check"></i> Tenant Created
                                        </span>
                                        @if($business->subdomain)
                                            <br><small class="text-muted">{{ $business->subdomain }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fa fa-clock-o"></i> No Tenant
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $business->subscriptions->count() }}</span>
                                    @if($business->subscriptions->where('status', 'approved')->count() > 0)
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    @if($business->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('superadmin.businesses.show', $business->id) }}">
                                                <i class="fa fa-eye m-r-5"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('superadmin.businesses.edit', $business->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            {{-- @if(!$business->tenant_id)
                                                <a class="dropdown-item" href="{{ route('superadmin.tenants.create', $business->id) }}">
                                                    <i class="fa fa-server m-r-5"></i> Create Tenant
                                                </a>
                                            @endif --}}
                                            @if($business->is_active)
                                                <form action="{{ route('superadmin.businesses.deactivate', $business->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fa fa-ban m-r-5"></i> Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('superadmin.businesses.activate', $business->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fa fa-check m-r-5"></i> Activate
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('superadmin.businesses.destroy', $business->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this business?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fa fa-trash-o m-r-5"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No businesses found.</td>
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

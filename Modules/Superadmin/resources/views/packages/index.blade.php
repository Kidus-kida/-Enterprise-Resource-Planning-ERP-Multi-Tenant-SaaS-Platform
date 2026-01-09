@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Packages Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Packages</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('superadmin.packages.create') }}" class="btn add-btn">
                        <i class="fa fa-plus"></i> Add Package
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

        <!-- Packages Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped custom-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Interval</th>
                                <th>Limits</th>
                                <th>Trial Days</th>
                                <th>Status</th>
                                <th>Subscriptions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $package)
                            <tr>
                                <td>{{ $package->id }}</td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('superadmin.packages.show', $package->id) }}">{{ $package->name }}</a>
                                    </h2>
                                    @if($package->is_private)
                                        <span class="badge bg-warning">Private</span>
                                    @endif
                                </td>
                                <td>{{ number_format($package->price, 2) }} ETB</td>
                                <td>{{ $package->interval_count }} {{ ucfirst($package->interval) }}</td>
                                <td>
                                    <small>
                                        <strong>Locations:</strong> {{ $package->location_count == 0 ? 'Unlimited' : $package->location_count }}<br>
                                        <strong>Users:</strong> {{ $package->user_count == 0 ? 'Unlimited' : $package->user_count }}<br>
                                        <strong>Products:</strong> {{ $package->product_count == 0 ? 'Unlimited' : $package->product_count }}
                                    </small>
                                </td>
                                <td>{{ $package->trial_days }} days</td>
                                <td>
                                    @if($package->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $package->subscriptions_count ?? 0 }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="material-icons">more_vert</i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('superadmin.packages.show', $package->id) }}">
                                                <i class="fa fa-eye m-r-5"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('superadmin.packages.edit', $package->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <form action="{{ route('superadmin.packages.toggle-active', $package->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fa fa-power-off m-r-5"></i> 
                                                    {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('superadmin.packages.destroy', $package->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this package?');">
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
                                <td colspan="9" class="text-center">No packages found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

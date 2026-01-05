@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Tenant Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tenant Management</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-primary">
                                <i class="fa fa-database"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Total Tenants</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-success">
                                <i class="fa fa-check-circle"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Active Tenants</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-warning">
                                <i class="fa fa-clock-o"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['pending'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Pending Setup</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-info">
                                <i class="fa fa-server"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['databases_created'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Databases Configured</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tenants List -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">All Tenants</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Business Name</th>
                                        <th>Subdomain</th>
                                        <th>Database</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tenants as $tenant)
                                        <tr>
                                            <td>{{ $tenant->id }}</td>
                                            <td>
                                                <a href="{{ route('superadmin.businesses.show', $tenant->business_id) }}">
                                                    {{ $tenant->business->name ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>
                                                <code>{{ $tenant->business->subdomain ?? 'N/A' }}</code>
                                            </td>
                                            <td>
                                                @if($tenant->data)
                                                    <span class="badge bg-success">
                                                        <i class="fa fa-check"></i> Configured
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fa fa-exclamation-triangle"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tenant->business->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $tenant->created_at->format('d M Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('superadmin.tenant-management.setup-wizard', $tenant->business_id) }}" 
                                                        class="btn btn-sm btn-primary" title="Setup Wizard">
                                                        <i class="fa fa-magic"></i>
                                                    </a>
                                                    @if(!$tenant->business->is_active)
                                                        <form action="{{ route('superadmin.tenant-management.destroy', $tenant->id) }}" 
                                                            method="POST" style="display:inline;"
                                                            onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No tenants found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $tenants->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

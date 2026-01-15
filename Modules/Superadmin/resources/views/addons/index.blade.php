@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add-ons</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Add-ons</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.addons.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create Add-on
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-puzzle-piece"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['total'] }}</h3>
                            <span>Total Add-ons</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-check-circle"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['active'] }}</h3>
                            <span>Active</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-times-circle"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ $stats['inactive'] }}</h3>
                            <span>Inactive</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                        <div class="dash-widget-info">
                            <h3>{{ number_format($stats['total_revenue'], 0) }} ETB</h3>
                            <span>Revenue Potential</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add-ons Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Module</th>
                                        <th>Price</th>
                                        <th>Features</th>
                                        <th>Status</th>
                                        <th>Sort Order</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($addons as $addon)
                                        <tr>
                                            <td>
                                                <strong>{{ $addon->name }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($addon->description, 50) }}</small>
                                            </td>
                                            <td>
                                                @if($addon->module)
                                                    <span class="badge bg-info">{{ $addon->module->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">No Module</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($addon->price, 0) }} ETB</strong></td>
                                            <td>
                                                @if($addon->features)
                                                    <span class="badge bg-light text-dark">{{ count($addon->features) }} features</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($addon->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $addon->sort_order }}</td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item" href="{{ route('superadmin.addons.show', $addon->id) }}">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('superadmin.addons.edit', $addon->id) }}">
                                                            <i class="fa fa-pencil"></i> Edit
                                                        </a>
                                                        <form action="{{ route('superadmin.addons.toggle-active', $addon->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fa fa-power-off"></i> {{ $addon->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('superadmin.addons.destroy', $addon->id) }}" method="POST" 
                                                            onsubmit="return confirm('Are you sure you want to delete this add-on?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No add-ons found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $addons->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

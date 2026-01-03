@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Module Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Modules</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.modules.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Module
                    </a>
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
                                <i class="fa fa-cubes"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Total Modules</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-info">
                                <i class="fa fa-star"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['core'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Core Modules</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon bg-warning">
                                <i class="fa fa-puzzle-piece"></i>
                            </span>
                            <div class="dash-count">
                                <h3>{{ $stats['optional'] }}</h3>
                            </div>
                        </div>
                        <div class="dash-widget-info">
                            <h6 class="text-muted">Optional Modules</h6>
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
                            <h6 class="text-muted">Active Modules</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules List -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">All Modules</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Module Name</th>
                                        <th>Key</th>
                                        <th>Type</th>
                                        <th>Routes</th>
                                        <th>Add-ons</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($modules as $module)
                                        <tr>
                                            <td>{{ $module->sort_order }}</td>
                                            <td>
                                                <i class="la {{ $module->icon ?? 'la-cube' }}"></i>
                                                <a href="{{ route('superadmin.modules.show', $module->id) }}">
                                                    {{ $module->name }}
                                                </a>
                                                @if($module->is_core)
                                                    <span class="badge bg-info ms-1">Core</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $module->key }}</code></td>
                                            <td>
                                                @if($module->is_core)
                                                    <span class="badge bg-info">Core</span>
                                                @else
                                                    <span class="badge bg-secondary">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($module->routes && count($module->routes) > 0)
                                                    <small class="text-muted">{{ count($module->routes) }} route(s)</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($module->addons && $module->addons->count() > 0)
                                                    <span class="badge bg-success">{{ $module->addons->count() }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($module->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('superadmin.modules.show', $module->id) }}" 
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('superadmin.modules.edit', $module->id) }}" 
                                                        class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('superadmin.modules.toggle-active', $module->id) }}" 
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="btn btn-sm {{ $module->is_active ? 'btn-warning' : 'btn-success' }}" 
                                                            title="{{ $module->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fa fa-{{ $module->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    @if(!$module->is_core)
                                                        <form action="{{ route('superadmin.modules.destroy', $module->id) }}" 
                                                            method="POST" style="display:inline;"
                                                            onsubmit="return confirm('Are you sure you want to delete this module?');">
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
                                            <td colspan="8" class="text-center text-muted">No modules found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $modules->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        <i class="la {{ $module->icon ?? 'la-cube' }}"></i> {{ $module->name }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.index') }}">Modules</a></li>
                        <li class="breadcrumb-item active">{{ $module->name }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('superadmin.modules.edit', $module->id) }}" class="btn btn-primary">
                        <i class="fa fa-pencil"></i> Edit
                    </a>
                    @if(!$module->is_core)
                        <form action="{{ route('superadmin.modules.destroy', $module->id) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this module?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Module Details -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Module Information</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="30%">Module ID:</th>
                                    <td>#{{ $module->id }}</td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $module->name }}</td>
                                </tr>
                                <tr>
                                    <th>Key:</th>
                                    <td><code>{{ $module->key }}</code></td>
                                </tr>
                                <tr>
                                    <th>Icon:</th>
                                    <td>
                                        @if($module->icon)
                                            <i class="la {{ $module->icon }} fa-2x"></i> 
                                            <code>{{ $module->icon }}</code>
                                        @else
                                            <span class="text-muted">No icon</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        @if($module->is_core)
                                            <span class="badge bg-info">Core Module</span>
                                        @else
                                            <span class="badge bg-secondary">Optional Module</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($module->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order:</th>
                                    <td>{{ $module->sort_order }}</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $module->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $module->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        @if($module->description)
                            <div class="alert alert-info mt-3">
                                <h6>Description:</h6>
                                <p class="mb-0">{{ $module->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <!-- Routes -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Route Patterns</h4>
                    </div>
                    <div class="card-body">
                        @if($module->routes && count($module->routes) > 0)
                            <ul class="list-group">
                                @foreach($module->routes as $route)
                                    <li class="list-group-item">
                                        <i class="fa fa-route text-primary"></i>
                                        <code>{{ $route }}</code>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No route patterns defined</p>
                        @endif
                    </div>
                </div>

                <!-- Permissions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Permissions</h4>
                    </div>
                    <div class="card-body">
                        @if($module->permissions && count($module->permissions) > 0)
                            <div class="row">
                                @foreach($module->permissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-light text-dark">
                                            <i class="fa fa-key text-warning"></i> {{ $permission }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No permissions defined</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Linked Add-ons -->
        @if($module->addons && $module->addons->count() > 0)
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Linked Add-ons ({{ $module->addons->count() }})</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Add-on Name</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Subscriptions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($module->addons as $addon)
                                            <tr>
                                                <td>
                                                    <strong>{{ $addon->name }}</strong>
                                                    <br><small class="text-muted">{{ $addon->description }}</small>
                                                </td>
                                                <td>{{ number_format($addon->price, 0) }} ETB</td>
                                                <td>
                                                    @if($addon->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $addon->subscriptions->count() }}</td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
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

    </div>
</div>
@endsection

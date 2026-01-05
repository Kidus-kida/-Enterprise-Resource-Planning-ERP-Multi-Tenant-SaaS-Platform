@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Edit Module</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.index') }}">Modules</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.modules.update', $module->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Module Details</h4>
                                    
                                    <div class="form-group">
                                        <label>Module Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                            value="{{ old('name', $module->name) }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Module Key <span class="text-danger">*</span></label>
                                        <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" 
                                            value="{{ old('key', $module->key) }}" required>
                                        @error('key')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Unique identifier (lowercase, no spaces)</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Icon Class</label>
                                        <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" 
                                            value="{{ old('icon', $module->icon) }}">
                                        @error('icon')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">LineAwesome icon class</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                            rows="3">{{ old('description', $module->description) }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                                            value="{{ old('sort_order', $module->sort_order) }}">
                                        @error('sort_order')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Routes & Permissions</h4>
                                    
                                    <div class="form-group">
                                        <label>Route Patterns</label>
                                        <textarea name="routes" class="form-control @error('routes') is-invalid @enderror" 
                                            rows="4">{{ old('routes', is_array($module->routes) ? implode(', ', $module->routes) : '') }}</textarea>
                                        @error('routes')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Comma-separated route patterns</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Permission Names</label>
                                        <textarea name="permissions" class="form-control @error('permissions') is-invalid @enderror" 
                                            rows="4">{{ old('permissions', is_array($module->permissions) ? implode(', ', $module->permissions) : '') }}</textarea>
                                        @error('permissions')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Comma-separated permission names</small>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_core" class="form-check-input" id="is_core" 
                                            value="1" {{ old('is_core', $module->is_core) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_core">
                                            <strong>Core Module</strong>
                                            <br><small class="text-muted">Core modules cannot be disabled or deleted</small>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                            value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong>
                                            <br><small class="text-muted">Module is available for use</small>
                                        </label>
                                    </div>

                                    @if($module->addons && $module->addons->count() > 0)
                                        <div class="alert alert-warning">
                                            <h6><i class="fa fa-exclamation-triangle"></i> Warning:</h6>
                                            <p class="mb-0">This module has {{ $module->addons->count() }} linked add-on(s). Changes may affect subscriptions.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Update Module</button>
                                <a href="{{ route('superadmin.modules.show', $module->id) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

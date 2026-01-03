@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Create Module</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.index') }}">Modules</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.modules.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Module Details</h4>
                                    
                                    <div class="form-group">
                                        <label>Module Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                            value="{{ old('name') }}" placeholder="e.g., Payroll Management" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Module Key <span class="text-danger">*</span></label>
                                        <input type="text" name="key" class="form-control @error('key') is-invalid @enderror" 
                                            value="{{ old('key') }}" placeholder="e.g., payroll" required>
                                        @error('key')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Unique identifier (lowercase, no spaces)</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Icon Class</label>
                                        <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" 
                                            value="{{ old('icon') }}" placeholder="e.g., la-money">
                                        @error('icon')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">LineAwesome icon class (e.g., la-money, la-users)</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                            rows="3" placeholder="Brief description of what this module does">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                                            value="{{ old('sort_order', 100) }}">
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
                                            rows="4" placeholder="payroll.*, payslips.*, payroll-allowances.*">{{ old('routes') }}</textarea>
                                        @error('routes')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Comma-separated route patterns (e.g., payroll.*, payslips.*)</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Permission Names</label>
                                        <textarea name="permissions" class="form-control @error('permissions') is-invalid @enderror" 
                                            rows="4" placeholder="view-payrolls, create-payrolls, edit-payrolls">{{ old('permissions') }}</textarea>
                                        @error('permissions')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Comma-separated permission names</small>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_core" class="form-check-input" id="is_core" 
                                            value="1" {{ old('is_core') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_core">
                                            <strong>Core Module</strong>
                                            <br><small class="text-muted">Core modules are included in all packages and cannot be disabled</small>
                                        </label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong>
                                            <br><small class="text-muted">Module is available for use</small>
                                        </label>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6><i class="fa fa-info-circle"></i> Note:</h6>
                                        <ul class="mb-0">
                                            <li>Module key must be unique</li>
                                            <li>Routes help protect access automatically</li>
                                            <li>Permissions link with Laravel's authorization</li>
                                            <li>Core modules cannot be deleted</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Create Module</button>
                                <a href="{{ route('superadmin.modules.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Create Package</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></li>
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
                        <form action="{{ route('superadmin.packages.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Basic Information</h4>
                                    
                                    <div class="form-group">
                                        <label>Package Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Price (ETB) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0) }}" required>
                                        @error('price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Interval <span class="text-danger">*</span></label>
                                                <select name="interval" class="form-control @error('interval') is-invalid @enderror" required>
                                                    <option value="days" {{ old('interval') == 'days' ? 'selected' : '' }}>Days</option>
                                                    <option value="months" {{ old('interval') == 'months' ? 'selected' : 'selected' }}>Months</option>
                                                    <option value="years" {{ old('interval') == 'years' ? 'selected' : '' }}>Years</option>
                                                </select>
                                                @error('interval')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Interval Count <span class="text-danger">*</span></label>
                                                <input type="number" name="interval_count" class="form-control @error('interval_count') is-invalid @enderror" value="{{ old('interval_count', 1) }}" required>
                                                @error('interval_count')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Trial Days</label>
                                        <input type="number" name="trial_days" class="form-control @error('trial_days') is-invalid @enderror" value="{{ old('trial_days', 0) }}">
                                        @error('trial_days')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                                        @error('sort_order')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Limits -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Limits (0 = Unlimited)</h4>
                                    
                                    <div class="form-group">
                                        <label>Location Count <span class="text-danger">*</span></label>
                                        <input type="number" name="location_count" class="form-control @error('location_count') is-invalid @enderror" value="{{ old('location_count', 0) }}" required>
                                        @error('location_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>User Count <span class="text-danger">*</span></label>
                                        <input type="number" name="user_count" class="form-control @error('user_count') is-invalid @enderror" value="{{ old('user_count', 0) }}" required>
                                        @error('user_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Product Count <span class="text-danger">*</span></label>
                                        <input type="number" name="product_count" class="form-control @error('product_count') is-invalid @enderror" value="{{ old('product_count', 0) }}" required>
                                        @error('product_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Invoice Count <span class="text-danger">*</span></label>
                                        <input type="number" name="invoice_count" class="form-control @error('invoice_count') is-invalid @enderror" value="{{ old('invoice_count', 0) }}" required>
                                        @error('invoice_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <h4 class="card-title mt-4">Module Permissions</h4>
                                    <small class="text-muted">Select modules included in this package</small>
                                    
                                    @if($modules->count() > 0)
                                        <div class="row mt-3">
                                            @foreach($modules as $module)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="custom_permissions[{{ $module->key }}]" 
                                                            value="1" class="form-check-input" 
                                                            id="perm_{{ $module->key }}" 
                                                            {{ old('custom_permissions.' . $module->key) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_{{ $module->key }}">
                                                            <i class="la {{ $module->icon ?? 'la-cube' }}"></i> {{ $module->name }}
                                                        </label>
                                                        @if($module->description)
                                                            <br><small class="text-muted">{{ $module->description }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning mt-3">
                                            <i class="fa fa-exclamation-triangle"></i> No modules found. Please run the module seeder first.
                                        </div>
                                    @endif

                                    <h4 class="card-title mt-4">Per-User Pricing</h4>
                                    <small class="text-muted">Enable dynamic pricing based on number of users</small>
                                    
                                    <div class="form-check mb-3 mt-3">
                                        <input type="checkbox" name="is_per_user_pricing" value="1" 
                                            class="form-check-input" id="is_per_user_pricing"
                                            {{ old('is_per_user_pricing') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_per_user_pricing">
                                            <strong>Enable Per-User Pricing</strong>
                                            <br><small class="text-muted">Charge based on number of users beyond minimum</small>
                                        </label>
                                    </div>

                                    <div id="per_user_fields" style="display: none;">
                                        <div class="form-group">
                                            <label>Minimum Users Included</label>
                                            <input type="number" name="min_users" class="form-control" 
                                                value="{{ old('min_users', 1) }}" min="1">
                                            <small class="text-muted">Number of users included in base price</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Price Per Additional User (ETB)</label>
                                            <input type="number" name="price_per_user" step="0.01" class="form-control" 
                                                value="{{ old('price_per_user', 0) }}" min="0">
                                            <small class="text-muted">Cost per user beyond minimum</small>
                                        </div>
                                    </div>

                                    <h4 class="card-title mt-4">Settings</h4>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="is_private" value="1" class="form-check-input" id="is_private" {{ old('is_private') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_private">Private Package</label>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Create Package</button>
                                <a href="{{ route('superadmin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('is_per_user_pricing');
    const perUserFields = document.getElementById('per_user_fields');
    
    function togglePerUserFields() {
        if (checkbox.checked) {
            perUserFields.style.display = 'block';
        } else {
            perUserFields.style.display = 'none';
        }
    }
    
    checkbox.addEventListener('change', togglePerUserFields);
    togglePerUserFields(); // Run on page load
});
</script>
@endpush
@endsection

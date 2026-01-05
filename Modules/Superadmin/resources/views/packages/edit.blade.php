@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Edit Package</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></li>
                        <li class="breadcrumb-item active">Edit - {{ $package->name }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.packages.update', $package->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Basic Information</h4>
                                    
                                    <div class="form-group">
                                        <label>Package Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $package->name) }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $package->description) }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Price (ETB) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $package->price) }}" required>
                                        @error('price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Interval <span class="text-danger">*</span></label>
                                                <select name="interval" class="form-control @error('interval') is-invalid @enderror" required>
                                                    <option value="days" {{ old('interval', $package->interval) == 'days' ? 'selected' : '' }}>Days</option>
                                                    <option value="months" {{ old('interval', $package->interval) == 'months' ? 'selected' : '' }}>Months</option>
                                                    <option value="years" {{ old('interval', $package->interval) == 'years' ? 'selected' : '' }}>Years</option>
                                                </select>
                                                @error('interval')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Interval Count <span class="text-danger">*</span></label>
                                                <input type="number" name="interval_count" class="form-control @error('interval_count') is-invalid @enderror" value="{{ old('interval_count', $package->interval_count) }}" required>
                                                @error('interval_count')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Trial Days</label>
                                        <input type="number" name="trial_days" class="form-control @error('trial_days') is-invalid @enderror" value="{{ old('trial_days', $package->trial_days) }}">
                                        @error('trial_days')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $package->sort_order) }}">
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
                                        <input type="number" name="location_count" class="form-control @error('location_count') is-invalid @enderror" value="{{ old('location_count', $package->location_count) }}" required>
                                        @error('location_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>User Count <span class="text-danger">*</span></label>
                                        <input type="number" name="user_count" class="form-control @error('user_count') is-invalid @enderror" value="{{ old('user_count', $package->user_count) }}" required>
                                        @error('user_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Product Count <span class="text-danger">*</span></label>
                                        <input type="number" name="product_count" class="form-control @error('product_count') is-invalid @enderror" value="{{ old('product_count', $package->product_count) }}" required>
                                        @error('product_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Invoice Count <span class="text-danger">*</span></label>
                                        <input type="number" name="invoice_count" class="form-control @error('invoice_count') is-invalid @enderror" value="{{ old('invoice_count', $package->invoice_count) }}" required>
                                        @error('invoice_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <h4 class="card-title mt-4">Module Permissions</h4>
                                    
                                    @php
                                        $permissions = old('custom_permissions', $package->custom_permissions ?? []);
                                    @endphp
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[contacts]" value="1" class="form-check-input" id="perm_contacts" {{ isset($permissions['contacts']) && $permissions['contacts'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_contacts">Contacts</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[products]" value="1" class="form-check-input" id="perm_products" {{ isset($permissions['products']) && $permissions['products'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_products">Products</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[pos]" value="1" class="form-check-input" id="perm_pos" {{ isset($permissions['pos']) && $permissions['pos'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_pos">POS</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[purchases]" value="1" class="form-check-input" id="perm_purchases" {{ isset($permissions['purchases']) && $permissions['purchases'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_purchases">Purchases</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[accounting]" value="1" class="form-check-input" id="perm_accounting" {{ isset($permissions['accounting']) && $permissions['accounting'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_accounting">Accounting</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[reports]" value="1" class="form-check-input" id="perm_reports" {{ isset($permissions['reports']) && $permissions['reports'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_reports">Reports</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[hr]" value="1" class="form-check-input" id="perm_hr" {{ isset($permissions['hr']) && $permissions['hr'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_hr">HR Management</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" name="custom_permissions[payroll]" value="1" class="form-check-input" id="perm_payroll" {{ isset($permissions['payroll']) && $permissions['payroll'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_payroll">Payroll</label>
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="card-title mt-4">Settings</h4>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="is_private" value="1" class="form-check-input" id="is_private" {{ old('is_private', $package->is_private) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_private">Private Package</label>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Update Package</button>
                                <a href="{{ route('superadmin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

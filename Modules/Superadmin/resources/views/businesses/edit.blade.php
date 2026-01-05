@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Edit Business</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.businesses.index') }}">Businesses</a></li>
                        <li class="breadcrumb-item active">Edit - {{ $business->name }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.businesses.update', $business->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Business Information</h4>
                                    
                                    <div class="form-group">
                                        <label>Business Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $business->name) }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $business->email) }}">
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $business->phone) }}">
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $business->address) }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Package & Status</h4>
                                    
                                    <div class="form-group">
                                        <label>Current Owner</label>
                                        <input type="text" class="form-control" value="{{ $business->owner->name ?? 'N/A' }} ({{ $business->owner->email ?? 'N/A' }})" disabled>
                                        <small class="form-text text-muted">Owner cannot be changed after creation</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Package</label>
                                        <select name="package_id" class="form-control @error('package_id') is-invalid @enderror">
                                            <option value="">No Package</option>
                                            @foreach($packages as $package)
                                                <option value="{{ $package->id }}" {{ old('package_id', $business->package_id) == $package->id ? 'selected' : '' }}>
                                                    {{ $package->name }} - {{ number_format($package->price, 2) }} ETB
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('package_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Changing package doesn't affect existing subscriptions</small>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6><i class="fa fa-info-circle"></i> Current Status:</h6>
                                        <ul class="mb-0">
                                            <li><strong>Status:</strong> {{ $business->is_active ? 'Active' : 'Inactive' }}</li>
                                            <li><strong>Tenant:</strong> {{ $business->tenant_id ? 'Created' : 'Not Created' }}</li>
                                            @if($business->subdomain)
                                                <li><strong>Subdomain:</strong> {{ $business->subdomain }}</li>
                                            @endif
                                            <li><strong>Subscriptions:</strong> {{ $business->subscriptions->count() }}</li>
                                            <li><strong>Created:</strong> {{ $business->created_at->format('d M Y') }}</li>
                                        </ul>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fa fa-exclamation-triangle"></i> Note:</h6>
                                        <ul class="mb-0">
                                            <li>To change subscription, go to Subscriptions page</li>
                                            <li>To manage tenant, use Tenant Management</li>
                                            <li>To activate/deactivate, use action menu in list</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Update Business</button>
                                <a href="{{ route('superadmin.businesses.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

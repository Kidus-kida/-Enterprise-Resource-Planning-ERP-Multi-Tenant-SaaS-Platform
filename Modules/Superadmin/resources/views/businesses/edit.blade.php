@extends('layouts.app')

@section('page-content')
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
                                <!-- Left Column: Business Information -->
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
                                        <input type="email" name="owner_email" class="form-control @error('owner_email') is-invalid @enderror" value="{{ old('owner_email', $business->owner_email ?? ($business->owner->email ?? '')) }}">
                                        @error('owner_email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Owner email used for invitations & subscription</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror" value="{{ old('owner_phone', $business->owner_phone ?? ($business->owner->phone ?? '')) }}">
                                        @error('owner_phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $business->owner->address ?? '') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column: Package & Status -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Package & Status</h4>
                                    
                                    <div class="form-group">
                                        <label>Owner First Name<span class="text-danger">*</span></label>
                                        <input type="text" name="owner_firstname" class="form-control @error('owner_firstname') is-invalid @enderror" value="{{ old('owner_firstname', $business->owner_firstname ?? ($business->owner->firstname ?? '')) }}">
                                        @error('owner_firstname')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Owner Last Name<span class="text-danger">*</span></label>
                                        <input type="text" name="owner_lastname" class="form-control @error('owner_lastname') is-invalid @enderror" value="{{ old('owner_lastname', $business->owner_lastname ?? ($business->owner->lastname ?? '')) }}">
                                        @error('owner_lastname')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
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

                            <!-- Add-ons Section (Optional) -->
                            @if($business->subscription)
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h4 class="card-title">Subscription Add-ons (Optional)</h4>
                                        <small class="text-muted">Manage add-ons for the current subscription</small>
                                        
                                        <div class="row mt-3">
                                            @forelse($addons as $addon)
                                                <div class="col-md-6">
                                                    <div class="form-check mb-3 border p-3 rounded">
                                                        <input type="checkbox" name="addons[]" value="{{ $addon->id }}" 
                                                            class="form-check-input" id="addon_{{ $addon->id }}"
                                                            {{ $business->subscription->addons->contains($addon->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label w-100" for="addon_{{ $addon->id }}">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <strong>{{ $addon->name }}</strong>
                                                                    <span class="badge bg-success ms-2">+{{ number_format($addon->price, 0) }} ETB</span>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted d-block mt-1">{{ $addon->description }}</small>
                                                            @if($addon->features)
                                                                <div class="mt-2">
                                                                    @foreach($addon->features as $feature)
                                                                        <span class="badge bg-light text-dark me-1">✓ {{ $feature }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <p class="text-muted">No add-ons available</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info mt-4">
                                    <i class="fa fa-info-circle"></i> No active subscription. Create a subscription first to manage add-ons.
                                </div>
                            @endif

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
@endsection

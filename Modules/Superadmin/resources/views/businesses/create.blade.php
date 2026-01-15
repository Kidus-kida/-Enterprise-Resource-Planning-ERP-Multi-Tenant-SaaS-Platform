@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Create Business</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.businesses.index') }}">Businesses</a></li>
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
                        <form action="{{ route('superadmin.businesses.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Business Information</h4>
                                    
                                    <div class="form-group">
                                        <label>Business Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="business_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" readonly>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Auto-populated from Business Owner</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" id="business_phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" readonly>
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Auto-populated from Business Owner</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" id="business_address" class="form-control @error('address') is-invalid @enderror" rows="3" readonly>{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Auto-populated from Business Owner</small>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Owner & Package</h4>
                                    
                                    <div class="form-group">
                                        <label>Business Owner <span class="text-danger">*</span></label>
                                        <select name="owner_id" id="owner_select" class="form-control @error('owner_id') is-invalid @enderror" required>
                                            <option value="">Select Owner</option>
                                            @foreach(\App\Models\User::orderBy('firstname')->get() as $user)
                                                <option value="{{ $user->id }}" 
                                                    data-email="{{ $user->email }}"
                                                    data-phone="{{ $user->phone }}"
                                                    data-address="{{ $user->address }}"
                                                    {{ old('owner_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('owner_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Select the user who will own this business</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Package <span class="text-danger">*</span></label>
                                        <select name="package_id" class="form-control @error('package_id') is-invalid @enderror" required id="package_select">
                                            <option value="">Select Package</option>
                                            @foreach($packages as $package)
                                                <option value="{{ $package->id }}" 
                                                    data-price="{{ $package->price }}"
                                                    data-interval="{{ $package->interval_count }} {{ ucfirst($package->interval) }}"
                                                    data-is-per-user-pricing="{{ $package->is_per_user_pricing }}"
                                                    data-price-per-user="{{ $package->price_per_user ?? 0 }}"
                                                    data-min-users="{{ $package->min_users ?? 1 }}"
                                                    {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                                    {{ $package->name }} - {{ number_format($package->price, 2) }} ETB
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('package_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div id="package_details" class="alert alert-info" style="display: none;">
                                        <h6>Package Details:</h6>
                                        <p class="mb-0" id="package_info"></p>
                                    </div>

                                    <div id="user_count_wrapper" class="form-group" style="display: none;">
                                        <label>Number of Users <span class="text-danger">*</span></label>
                                        <input type="number" name="subscribed_user_count" id="user_count_input" 
                                            class="form-control" value="{{ old('subscribed_user_count', 1) }}" min="1">
                                        <small class="form-text text-muted" id="user_count_help"></small>
                                        <div id="dynamic_price_display" class="mt-2 font-weight-bold text-success" style="display: none;">
                                            Calculated Price: <span id="calculated_price">0</span> ETB
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Subscription Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                        @error('start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">This will create an approved subscription automatically</small>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fa fa-info-circle"></i> Note:</h6>
                                        <ul class="mb-0">
                                            <li>Business will be created with status <strong>Active</strong></li>
                                            <li>A subscription will be automatically created</li>
                                            <li>Tenant setup needs to be done separately after creation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Add-ons Section (Optional) -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h4 class="card-title">Add-ons (Optional)</h4>
                                    <small class="text-muted">Select additional modules to enhance the package</small>
                                    
                                    <div class="row mt-3">
                                        @forelse($addons as $addon)
                                            <div class="col-md-6">
                                                <div class="form-check mb-3 border p-3 rounded">
                                                    <input type="checkbox" name="addons[]" value="{{ $addon->id }}" 
                                                        class="form-check-input" id="addon_{{ $addon->id }}">
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

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Create Business</button>
                                <a href="{{ route('superadmin.businesses.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<script>
// Auto-populate business contact fields from selected owner
document.getElementById('owner_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const emailField = document.getElementById('business_email');
    const phoneField = document.getElementById('business_phone');
    const addressField = document.getElementById('business_address');
    
    if (this.value) {
        emailField.value = selected.dataset.email || '';
        phoneField.value = selected.dataset.phone || '';
        addressField.value = selected.dataset.address || '';
    } else {
        emailField.value = '';
        phoneField.value = '';
        addressField.value = '';
    }
});

// Package details display and dynamic pricing
document.getElementById('package_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const details = document.getElementById('package_details');
    const info = document.getElementById('package_info');
    const userCountWrapper = document.getElementById('user_count_wrapper');
    const userCountInput = document.getElementById('user_count_input');
    
    if (this.value) {
        const basePrice = parseFloat(selected.dataset.price);
        const interval = selected.dataset.interval;
        
        // Dynamic Pricing Data
        const isPerUser = selected.dataset.isPerUserPricing === '1';
        const pricePerUser = parseFloat(selected.dataset.pricePerUser) || 0;
        const minUsers = parseInt(selected.dataset.minUsers) || 1;
        
        let detailsText = `<strong>Base Price:</strong> ${basePrice.toLocaleString()} ETB<br><strong>Billing:</strong> ${interval}`;
        info.innerHTML = detailsText;
        details.style.display = 'block';

        if (isPerUser) {
            userCountWrapper.style.display = 'block';
            document.getElementById('user_count_help').textContent = 
                `Base price includes ${minUsers} users. Additional users charged at ${pricePerUser} ETB/user.`;
            userCountInput.min = 1;
            
            // Trigger calculation
            calculateDynamicPrice();
        } else {
            userCountWrapper.style.display = 'none';
            document.getElementById('dynamic_price_display').style.display = 'none';
        }
    } else {
        details.style.display = 'none';
        userCountWrapper.style.display = 'none';
    }
});

// Dynamic price calculator
document.getElementById('user_count_input').addEventListener('input', calculateDynamicPrice);

function calculateDynamicPrice() {
    const packageSelect = document.getElementById('package_select');
    const selected = packageSelect.options[packageSelect.selectedIndex];
    
    if (!packageSelect.value || selected.dataset.isPerUserPricing !== '1') return;

    const basePrice = parseFloat(selected.dataset.price);
    const pricePerUser = parseFloat(selected.dataset.pricePerUser) || 0;
    const minUsers = parseInt(selected.dataset.minUsers) || 1;
    const userCount = parseInt(document.getElementById('user_count_input').value) || 1;
    
    let finalPrice = basePrice;
    
    if (userCount > minUsers) {
        const additionalUsers = userCount - minUsers;
        finalPrice += (additionalUsers * pricePerUser);
    }
    
    const displayEl = document.getElementById('dynamic_price_display');
    const priceEl = document.getElementById('calculated_price');
    
    displayEl.style.display = 'block';
    priceEl.textContent = finalPrice.toLocaleString();
}

// Trigger owner change on page load if there's an old value
window.addEventListener('DOMContentLoaded', function() {
    const ownerSelect = document.getElementById('owner_select');
    if (ownerSelect.value) {
        ownerSelect.dispatchEvent(new Event('change'));
    }
});
</script>

@endsection

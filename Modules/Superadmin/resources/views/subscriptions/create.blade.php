@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Create Subscription</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.subscriptions.index') }}">Subscriptions</a></li>
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
                        <form action="{{ route('superadmin.subscriptions.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Subscription Details</h4>
                                    
                                    <div class="form-group">
                                        <label>Business <span class="text-danger">*</span></label>
                                        <select name="business_id" class="form-control @error('business_id') is-invalid @enderror" required id="business_select">
                                            <option value="">Select Business</option>
                                            @foreach($businesses as $business)
                                                <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                                    {{ $business->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('business_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Package <span class="text-danger">*</span></label>
                                        <select name="package_id" class="form-control @error('package_id') is-invalid @enderror" required id="package_select">
                                            <option value="">Select Package</option>
                                            @foreach($packages as $package)
                                                <option value="{{ $package->id }}" 
                                                    data-price="{{ $package->price }}"
                                                    data-interval="{{ $package->interval }}"
                                                    data-interval-count="{{ $package->interval_count }}"
                                                    data-is-per-user-pricing="{{ $package->is_per_user_pricing }}"
                                                    data-price-per-user="{{ $package->price_per_user ?? 0 }}"
                                                    data-min-users="{{ $package->min_users ?? 1 }}"
                                                    {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                                    {{ $package->name }} - {{ number_format($package->price, 2) }} ETB/{{ $package->interval_count }} {{ ucfirst($package->interval) }}
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
                                    </div>

                                    <div class="form-group">
                                        <label>Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                        @error('start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">End date will be calculated automatically based on package interval</small>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Status & Notes</h4>
                                    
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="waiting" {{ old('status') == 'waiting' ? 'selected' : '' }}>Waiting (Pending Approval)</option>
                                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : 'selected' }}>Approved</option>
                                            <option value="declined" {{ old('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Select 'Approved' to activate immediately</small>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fa fa-info-circle"></i> Important Notes:</h6>
                                        <ul class="mb-0">
                                            <li>End date will be calculated automatically</li>
                                            <li>If status is 'Approved', business will be activated</li>
                                            <li>Package details will be saved as a snapshot</li>
                                            <li>Module permissions will be copied from the package</li>
                                        </ul>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6><i class="fa fa-lightbulb-o"></i> Workflow:</h6>
                                        <ol class="mb-0">
                                            <li>Create subscription with selected package</li>
                                            <li>System calculates end date based on package interval</li>
                                            <li>If approved, business becomes active</li>
                                            <li>Module access is granted based on package permissions</li>
                                        </ol>
                                    </div>

                                    <!-- Add-ons Section -->
                                    <h4 class="card-title mt-4">Add-ons (Optional)</h4>
                                    <small class="text-muted">Select additional modules to enhance the package</small>
                                    
                                    <div class="mt-3" id="addons-list">
                                        @foreach($addons as $addon)
                                            <div class="form-check mb-3 border p-3 rounded addon-item" data-addon-price="{{ $addon->price }}">
                                                <input type="checkbox" name="addons[]" value="{{ $addon->id }}" 
                                                    class="form-check-input addon-checkbox" id="addon_{{ $addon->id }}">
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
                                        @endforeach
                                    </div>

                                    <!-- Price Summary -->
                                    <div class="alert alert-success mt-3">
                                        <h5 class="mb-2">Price Summary</h5>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Base Package:</span>
                                            <strong id="base-price-display">0 ETB</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 text-muted small" id="user-price-breakdown" style="display: none;">
                                            <span>User Pricing:</span>
                                            <span id="user-price-details"></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Add-ons:</span>
                                            <strong id="addons-price-display">0 ETB</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <h5>Total Price:</h5>
                                            <h5 class="text-success" id="total-price-display">0 ETB</h5>
                                        </div>
                                        <small class="text-muted">Per billing cycle</small>
                                    </div>
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">Create Subscription</button>
                                <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<script>
let basePrice = 0;
let currentPackage = null;

// Package selection handler
document.getElementById('package_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const details = document.getElementById('package_details');
    const info = document.getElementById('package_info');
    const userCountWrapper = document.getElementById('user_count_wrapper');
    
    if (this.value) {
        // Store package data
        currentPackage = {
            price: parseFloat(selected.dataset.price) || 0,
            interval: selected.dataset.interval,
            intervalCount: selected.dataset.intervalCount,
            isPerUserPricing: selected.dataset.isPerUserPricing === '1',
            pricePerUser: parseFloat(selected.dataset.pricePerUser) || 0,
            minUsers: parseInt(selected.dataset.minUsers) || 1
        };

        basePrice = currentPackage.price;
        info.innerHTML = `<strong>Base Price:</strong> ${basePrice.toLocaleString()} ETB<br><strong>Billing Cycle:</strong> ${currentPackage.intervalCount} ${currentPackage.interval}`;
        details.style.display = 'block';

        // Toggle user count input
        if (currentPackage.isPerUserPricing) {
            userCountWrapper.style.display = 'block';
            document.getElementById('user_count_help').textContent = 
                `Base price includes ${currentPackage.minUsers} users. Additional users charged at ${currentPackage.pricePerUser} ETB/user.`;
            document.getElementById('user_count_input').min = 1;
        } else {
            userCountWrapper.style.display = 'none';
        }

        updatePriceSummary();
    } else {
        basePrice = 0;
        currentPackage = null;
        details.style.display = 'none';
        userCountWrapper.style.display = 'none';
        updatePriceSummary();
    }
});

// User count input handler
document.getElementById('user_count_input').addEventListener('input', updatePriceSummary);

// Addon checkbox handler
document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updatePriceSummary);
});

function updatePriceSummary() {
    if (!currentPackage) {
        document.getElementById('base-price-display').textContent = '0 ETB';
        document.getElementById('addons-price-display').textContent = '0 ETB';
        document.getElementById('total-price-display').textContent = '0 ETB';
        document.getElementById('user-price-breakdown').style.display = 'none';
        return;
    }

    let calculatedBasePrice = currentPackage.price;
    let userBreakdown = '';
    let showBreakdown = false;

    // Calculate dynamic base price if per-user pricing
    if (currentPackage.isPerUserPricing) {
        const userCount = parseInt(document.getElementById('user_count_input').value) || 1;
        
        if (userCount > currentPackage.minUsers) {
            const additionalUsers = userCount - currentPackage.minUsers;
            const extraCost = additionalUsers * currentPackage.pricePerUser;
            calculatedBasePrice += extraCost;
            
            userBreakdown = `(+${additionalUsers} users × ${currentPackage.pricePerUser} ETB)`;
            showBreakdown = true;
        }
    }

    // Calculate addons price
    let addonsPrice = 0;
    document.querySelectorAll('.addon-checkbox:checked').forEach(checkbox => {
        const addonItem = checkbox.closest('.addon-item');
        const price = parseFloat(addonItem.dataset.addonPrice) || 0;
        addonsPrice += price;
    });
    
    const totalPrice = calculatedBasePrice + addonsPrice;
    
    // Update display
    document.getElementById('base-price-display').textContent = calculatedBasePrice.toLocaleString() + ' ETB';
    document.getElementById('addons-price-display').textContent = addonsPrice.toLocaleString() + ' ETB';
    document.getElementById('total-price-display').textContent = totalPrice.toLocaleString() + ' ETB';
    
    const breakdownEl = document.getElementById('user-price-breakdown');
    if (showBreakdown) {
        breakdownEl.style.display = 'flex';
        document.getElementById('user-price-details').textContent = userBreakdown;
    } else {
        breakdownEl.style.display = 'none';
    }
}
</script>

@endsection

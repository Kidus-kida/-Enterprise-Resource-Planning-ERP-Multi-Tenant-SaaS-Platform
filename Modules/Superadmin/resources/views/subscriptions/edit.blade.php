@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Edit Subscription</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.subscriptions.index') }}">Subscriptions</a></li>
                        <li class="breadcrumb-item active">Edit #{{ $subscription->id }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('superadmin.subscriptions.update', $subscription->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Subscription Details</h4>
                                    
                                    <div class="form-group">
                                        <label>Business</label>
                                        <input type="text" class="form-control" value="{{ $subscription->business->name ?? 'N/A' }}" disabled>
                                        <small class="form-text text-muted">Business cannot be changed after creation</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Package</label>
                                        <input type="text" class="form-control" value="{{ $subscription->package->name ?? 'N/A' }}" disabled>
                                        <small class="form-text text-muted">Package cannot be changed. Create new subscription to change package.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $subscription->start_date ? $subscription->start_date->format('Y-m-d') : '') }}" required>
                                        @error('start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>End Date <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $subscription->end_date ? $subscription->end_date->format('Y-m-d') : '') }}" required>
                                        @error('end_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Status</h4>
                                    
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="waiting" {{ old('status', $subscription->status) == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                            <option value="approved" {{ old('status', $subscription->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="declined" {{ old('status', $subscription->status) == 'declined' ? 'selected' : '' }}>Declined</option>
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="alert alert-info">
                                        <h6><i class="fa fa-info-circle"></i> Current Info:</h6>
                                        <ul class="mb-0">
                                            <li><strong>Created:</strong> {{ $subscription->created_at->format('d M Y, h:i A') }}</li>
                                            <li><strong>Last Updated:</strong> {{ $subscription->updated_at->format('d M Y, h:i A') }}</li>
                                            @if($subscription->isExpired())
                                                <li class="text-danger"><strong>Status:</strong> Expired</li>
                                            @elseif($subscription->isActive())
                                                <li class="text-success"><strong>Status:</strong> Active</li>
                                            @endif
                                        </ul>
                                    </div>

                                    <div class="alert alert-warning">
                                        <h6><i class="fa fa-exclamation-triangle"></i> Warning:</h6>
                                        <ul class="mb-0">
                                            <li>Changing status to 'Approved' will activate the business</li>
                                            <li>Changing dates affects subscription validity</li>
                                            <li>Module access is controlled by package permissions</li>
                                        </ul>
                                    </div>

                                    <!-- Add-ons Section -->
                                    <h5 class="mt-4">Add-ons (Optional)</h5>
                                    <small class="text-muted">Select additional modules to enhance the package</small>
                                    
                                    <div class="mt-3" id="addons-list">
                                        @foreach($addons as $addon)
                                            <div class="form-check mb-3 border p-3 rounded addon-item" data-addon-price="{{ $addon->price }}">
                                                <input type="checkbox" name="addons[]" value="{{ $addon->id }}" 
                                                    class="form-check-input addon-checkbox" id="addon_{{ $addon->id }}"
                                                    {{ in_array($addon->id, $selectedAddonIds) ? 'checked' : '' }}>
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
                                            <strong id="base-price-display">{{ number_format($subscription->package->price ?? 0, 0) }} ETB</strong>
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
                                <button type="submit" class="btn btn-primary submit-btn">Update Subscription</button>
                                <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@push('scripts')
<script>
let basePrice = {{ $subscription->package->price ?? 0 }};

// Addon checkbox handler
document.querySelectorAll('.addon-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updatePriceSummary);
});

function updatePriceSummary() {
    let addonsPrice = 0;
    
    // Calculate total addons price
    document.querySelectorAll('.addon-checkbox:checked').forEach(checkbox => {
        const addonItem = checkbox.closest('.addon-item');
        const price = parseFloat(addonItem.dataset.addonPrice) || 0;
        addonsPrice += price;
    });
    
    const totalPrice = basePrice + addonsPrice;
    
    // Update display
    document.getElementById('base-price-display').textContent = basePrice.toLocaleString() + ' ETB';
    document.getElementById('addons-price-display').textContent = addonsPrice.toLocaleString() + ' ETB';
    document.getElementById('total-price-display').textContent = totalPrice.toLocaleString() + ' ETB';
}

// Initialize on page load
updatePriceSummary();
</script>
@endpush
@endsection

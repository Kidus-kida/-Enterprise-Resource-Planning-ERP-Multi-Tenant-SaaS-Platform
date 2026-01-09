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
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h4 class="card-title">Owner & Package</h4>
                                    
                                    <div class="form-group">
                                        <label>Business Owner <span class="text-danger">*</span></label>
                                        <select name="owner_id" class="form-control @error('owner_id') is-invalid @enderror" required>
                                            <option value="">Select Owner</option>
                                            @foreach(\App\Models\User::orderBy('firstname')->get() as $user)
                                                <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>
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

@push('scripts')
<script>
document.getElementById('package_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const details = document.getElementById('package_details');
    const info = document.getElementById('package_info');
    
    if (this.value) {
        const price = selected.dataset.price;
        const interval = selected.dataset.interval;
        info.innerHTML = `<strong>Price:</strong> ${parseFloat(price).toLocaleString()} ETB<br><strong>Billing:</strong> ${interval}`;
        details.style.display = 'block';
    } else {
        details.style.display = 'none';
    }
});
</script>
@endpush
@endsection

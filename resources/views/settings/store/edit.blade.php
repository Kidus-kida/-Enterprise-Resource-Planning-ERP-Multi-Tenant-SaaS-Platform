@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Store</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ action([\App\Http\Controllers\StoreController::class, 'index']) }}">Stores</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ action([\App\Http\Controllers\StoreController::class, 'update'], [$store->id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary">Store Information</h4>
                                    <hr>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Business Location <span class="text-danger">*</span></label>
                                        <select name="location_id" class="form-control select" required>
                                            <option value="">Select Location</option>
                                            @foreach ($locations as $id => $name)
                                                <option value="{{ $id }}" 
                                                    {{ old('location_id', $store->location_id) == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('location_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                            placeholder="Store Name" value="{{ old('name', $store->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" placeholder="Address"
                                            value="{{ old('address', $store->address) }}">
                                        @error('address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" name="contact_number" class="form-control"
                                            placeholder="Contact Number" 
                                            value="{{ old('contact_number', $store->contact_number) }}">
                                        @error('contact_number')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Stock</label>
                                        <input type="number" name="stock" class="form-control" placeholder="0"
                                            value="{{ old('stock', $store->stock) }}" min="0">
                                        @error('stock')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="status" id="status"
                                                value="1" {{ old('status', $store->status) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <a href="{{ action([\App\Http\Controllers\StoreController::class, 'index']) }}"
                                        class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Store</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        if ($('.select').length > 0) {
            $('.select').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            });
        }
    </script>
@endpush

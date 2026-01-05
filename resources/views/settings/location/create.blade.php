@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Business Location</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ action([\App\Http\Controllers\BusinessLocationController::class, 'index']) }}">Business
                                Locations</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ action([\App\Http\Controllers\BusinessLocationController::class, 'store']) }}"
                            method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary">General Information</h4>
                                    <hr>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                            placeholder="Location Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Location ID</label>
                                        <input type="text" name="location_id" class="form-control"
                                            placeholder="Auto-generated if blank">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Landmark</label>
                                        <input type="text" name="landmark" class="form-control" placeholder="Landmark">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control" required
                                            placeholder="City">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Zip Code <span class="text-danger">*</span></label>
                                        <input type="text" name="zip_code" class="form-control" required
                                            placeholder="Zip Code">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" name="state" class="form-control" required
                                            placeholder="State">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Country <span class="text-danger">*</span></label>
                                        <input type="text" name="country" class="form-control" required
                                            placeholder="Country">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Mobile</label>
                                        <input type="text" name="mobile" class="form-control" placeholder="Mobile">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Alternate Number</label>
                                        <input type="text" name="alternate_number" class="form-control"
                                            placeholder="Alternate Number">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Website</label>
                                        <input type="text" name="website" class="form-control" placeholder="Website">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <h4 class="card-title text-primary">Settings</h4>
                                    <hr>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Invoice Scheme <span
                                                class="text-danger">*</span></label>
                                        <select name="invoice_scheme_id" class="form-control select" required>
                                            <option value="">Select Scheme</option>
                                            @foreach ($invoice_schemes as $id => $scheme)
                                                <option value="{{ $id }}">{{ $scheme }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Invoice Layout <span
                                                class="text-danger">*</span></label>
                                        <select name="invoice_layout_id" class="form-control select" required>
                                            <option value="">Select Layout</option>
                                            @foreach ($invoice_layouts as $id => $layout)
                                                <option value="{{ $id }}">{{ $layout }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Selling Price Group</label>
                                        <select name="selling_price_group_id" class="form-control select">
                                            <option value="">Default Selling Price</option>
                                            @foreach ($price_groups as $id => $pg)
                                                <option value="{{ $id }}">{{ $pg }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Save Location</button>
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

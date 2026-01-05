@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Add Invoice Layout</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']) }}">Invoice
                                Settings</a></li>
                        <li class="breadcrumb-item active">Add Layout</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ action([\App\Http\Controllers\InvoiceLayoutController::class, 'store']) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Layout Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                            placeholder="Layout Name">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Design <span class="text-danger">*</span></label>
                                        <select name="design" class="form-control select" required>
                                            @foreach ($designs as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Header</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Header Text</label>
                                        <textarea name="header_text" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                                <!-- More fields can be added here mirroring Old ERP -->
                                <div class="col-sm-3">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" name="show_business_name"
                                            value="1" id="show_business_name" checked>
                                        <label class="form-check-label" for="show_business_name">Show Business Name</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" name="show_location_name"
                                            value="1" id="show_location_name" checked>
                                        <label class="form-check-label" for="show_location_name">Show Location Name</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" name="show_logo" value="1"
                                            id="show_logo">
                                        <label class="form-check-label" for="show_logo">Show Logo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add more sections for Invoice Info, Product Table, Footer, etc. -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Invoice Info</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Invoice Heading</label>
                                        <input type="text" name="invoice_heading" class="form-control" value="Invoice"
                                            placeholder="Invoice Heading">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Invoice No. Label</label>
                                        <input type="text" name="invoice_no_prefix" class="form-control"
                                            value="Invoice No." placeholder="Invoice No. Label">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date Label</label>
                                        <input type="text" name="date_label" class="form-control" value="Date"
                                            placeholder="Date Label">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 text-end mb-4">
                    <button type="submit" class="btn btn-primary">Save Layout</button>
                </div>
            </div>
        </form>
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

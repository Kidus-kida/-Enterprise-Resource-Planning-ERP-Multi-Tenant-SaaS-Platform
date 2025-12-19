@extends('layouts.app')

@section('title', __('Bulk Purchase Import'))

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="page-title">{{ __('Bulk Purchase Import') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchases') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Bulk Import') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            @if(session('status'))
                <div class="alert alert-{{ session('status')['success'] ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                    {{ session('status')['msg'] }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">{{ __('Upload CSV File') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase.bulk_import_post') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label font-weight-bold">{{ __('Select CSV File') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="bulk_purchase_csv" class="form-control" required accept=".csv">
                                    <small class="text-muted">{{ __('Max file size: 5MB') }}</small>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary px-4 py-2 mr-2">
                                        <i class="la la-upload"></i> {{ __('Import Purchases') }}
                                    </button>
                                    <a href="{{ route('purchase.download_template') }}" class="btn btn-success px-4 py-2">
                                        <i class="la la-download"></i> {{ __('Download Template') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 border-start ps-4">
                                <h6 class="font-weight-bold mb-3">{{ __('Instructions') }}</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="la la-check-circle text-success"></i> {{ __('Download the template file first.') }}</li>
                                    <li class="mb-2"><i class="la la-check-circle text-success"></i> {{ __('Fill in the data according to the headers.') }}</li>
                                    <li class="mb-2"><i class="la la-check-circle text-success"></i> {{ __('Ensure the SKU or Product Name matches existing products.') }}</li>
                                    <li class="mb-2"><i class="la la-check-circle text-success"></i> {{ __('Group multiple items under same invoice number.') }}</li>
                                    <li class="mb-2"><i class="la la-check-circle text-success"></i> {{ __('Date format should be YYYY-MM-DD.') }}</li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">{{ __('Column Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped custom-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ __('Column Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>Invoice No.</code></td>
                                    <td>{{ __('Unique identifier for the purchase. Multiple rows with same Invoice No. will be grouped.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Supplier Name</code></td>
                                    <td>{{ __('Name of the supplier. Will be created if not exists.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Supplier Phone Number</code></td>
                                    <td>{{ __('Mobile number to find/create supplier.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Supplier Email</code></td>
                                    <td>{{ __('Email identifier for the supplier.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Sale Date</code></td>
                                    <td>{{ __('Purchase date (YYYY-MM-DD).') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Product Name</code></td>
                                    <td>{{ __('Name of the product (must match existing if Product SKU is empty).') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Product SKU</code></td>
                                    <td>{{ __('Product SKU (preferred for matching).') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Quantity</code></td>
                                    <td>{{ __('Quantity purchased.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Product Unit</code></td>
                                    <td>{{ __('Unit of measure (e.g., Pcs, Kg).') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Unit Price</code></td>
                                    <td>{{ __('Unit cost price.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Item Tax</code></td>
                                    <td>{{ __('Tax name for the item.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Item Discount</code></td>
                                    <td>{{ __('Discount amount for the item.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Item Description</code></td>
                                    <td>{{ __('Internal notes for the item.') }}</td>
                                </tr>
                                <tr>
                                    <td><code>Order Total</code></td>
                                    <td>{{ __('Total amount for the entire invoice.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

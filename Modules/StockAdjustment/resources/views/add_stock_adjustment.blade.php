@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Stock-Adjustment') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item active">{{ __('All stock adjustments') }}</li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('stockadjustment.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Back') }}
                </a>
            </div>
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card p-3">
                <h5 class="mb-3">{{ __('Add Stock Adjustment') }}</h5>

                <form action="{{ route('stockadjustment.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label>Business Location:*</label>
                            <select name="business_location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                @foreach($business_locations ?? [] as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Store:*</label>
                            <select name="store_id" class="form-control" required>
                                <option value="">Select Store</option>
                                @foreach($stores ?? [] as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Reference No:</label>
                            <input type="text" name="reference_no" class="form-control" value="{{ $reference_no ?? 'SA'.date('Y').'/'.str_pad($next_id ?? 1, 4, '0', STR_PAD_LEFT) }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Date:*</label>
                            <input type="datetime-local" name="date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label>Adjustment Type:*</label>
                            <select name="adjustment_type" class="form-control" required>
                                <option value="">Please Select</option>
                                <option value="increase">Increase</option>
                                <option value="decrease">Decrease</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Stock Adjustment Type:*</label>
                            <select name="stock_adjustment_type" class="form-control" required>
                                <option value="">Please Select</option>
                                @foreach($adjustment_types ?? [] as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Search Products -->
                    <div class="mb-3">
                        <label>Search Products</label>
                        <input type="text" name="product_search" id="product_search" class="form-control" placeholder="Search products for stock adjustment">
                    </div>

                    <!-- Products Table -->
                    <div class="table-responsive mb-3">
                        {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label>Total amount recovered:</label>
                            <input type="number" name="total_amount" class="form-control" value="0" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Reason:</label>
                            <textarea name="reason" class="form-control" placeholder="Reason"></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <a href="{{ route('stockadjustment.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush

@extends('layouts.app')
@section('title', __('lang_v1.add_stock_transfer'))

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">Respond to Request</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('stock-transfers-request.index') }}">Stock
                        Transfer Requests</a>
                </li>
                <li class="breadcrumb-item active">
                    Respond to Request
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <form id="transferForm" method="POST" action="{{ route('stock-transfer.savetransfer') }}">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request_transfer->id }}">
            <input type="hidden" id="final_total" name="final_total" value="0">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="transaction_date">@lang('messages.date'):*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="transaction_date" id="transaction_date" class="form-control"
                                        value="{{ @format_datetime('now') }}" readonly required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="ref_no">Reference No:</label>
                                <input class="form-control" type="text" name="ref_no" id="ref_no" placeholder="Reference No"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="location_id">@lang('lang_v1.location_from'):*</label>
                                <select name="location_id" id="location_id" class="form-control select2" required>
                                    <option value="">@lang('messages.please_select')</option>
                                    @foreach($business_locations as $id => $name)
                                        <option value="{{ $id }}" @if($request_transfer->request_to_location == $id) selected
                                        @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="from_store">@lang('lang_v1.from_store'):*</label>
                                <select name="from_store" id="from_store" class="form-control select2" required>
                                    <option value="">@lang('messages.please_select')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="transfer_location_id">@lang('lang_v1.location_to'):*</label>
                                <select name="transfer_location_id" id="transfer_location_id" class="form-control select2"
                                    required>
                                    <option value="">@lang('messages.please_select')</option>
                                    @foreach($business_locations as $id => $name)
                                        <option value="{{ $id }}" @if($request_transfer->request_location == $id) selected @endif>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="to_store_display">To Store:</label>
                                <input type="text" id="to_store_display" class="form-control" value="{{ $to_store->name }}"
                                    readonly>
                                <input type="hidden" name="to_store" id="to_store" value="{{ $to_store->id }}">
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary mt-4 mb-3">Product</h5>
                    <div class="row">
                        <input type="hidden" name="products[0][product_id]" id="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="products[0][variation_id]" value="{{ $variation_id->id }}">
                        <input type="hidden" name="products[0][enable_stock]" value="{{ $product->enable_stock }}">

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Product Name:</label>
                                <input class="form-control" type="text" value="{{ $product->name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Current Balance:</label>
                                <input id="current_balance" class="form-control" type="text" value="{{ $product->balance }}"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Unit:</label>
                                <input class="form-control" type="text" value="{{ $product->unit->actual_name }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Qty Requested:</label>
                                <input id="quantity_requested" class="form-control" type="text" name="products[0][quantity]"
                                    value="{{ $request_transfer->qty }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Unit Price:</label>
                                <input id="unit_price" class="form-control" type="text" name="products[0][unit_price]"
                                    value="{{ $variation_id->default_purchase_price }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Total Price:</label>
                                <input id="total_price_display" class="form-control" type="text" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="shipping_charges">@lang('lang_v1.shipping_charges'):</label>
                                <input type="text" name="shipping_charges" id="shipping_charges" value="0"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label" for="additional_notes">@lang('purchase.additional_notes'):</label>
                                <textarea name="additional_notes" id="additional_notes" class="form-control" rows="2"
                                    placeholder="Enter any additional notes here"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            function updateTotals() {
                var unitPrice = parseFloat($('#unit_price').val()) || 0;
                var qtyRequested = parseFloat($('#quantity_requested').val()) || 0;
                var totalPrice = unitPrice * qtyRequested;
                $('#total_price_display').val(totalPrice.toFixed(2));
                $('#final_total').val(totalPrice.toFixed(2));
            }

            updateTotals();

            $('#quantity_requested').on('input', function () {
                updateTotals();
            });

            $('#transferForm').on('submit', function (e) {
                var currentBalance = parseFloat($('#current_balance').val()) || 0;
                var quantityRequested = parseFloat($('#quantity_requested').val()) || 0;

                if (quantityRequested > currentBalance) {
                    e.preventDefault();
                    swal({
                        title: "Error",
                        text: "The quantity requested cannot be greater than the current balance of " + currentBalance,
                        icon: "error"
                    });
                }
            });

            $('#from_store').change(function () {
                var fromStoreId = $(this).val();
                var productId = $('#product_id').val();
                if (fromStoreId && productId) {
                    $.ajax({
                        url: '/stock-transfers-request/get-product-balance',
                        type: 'GET',
                        data: {
                            store_id: fromStoreId,
                            product_id: productId
                        },
                        success: function (response) {
                            $('#current_balance').val(response.balance);
                        }
                    });
                }
            });

            $('#location_id').change(function () {
                var location_id = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: '/stock-transfer/get_transfer_location/' + location_id,
                    success: function (result) {
                        $('#transfer_location_id').empty();
                        $.each(result, function (i, location) {
                            $('#transfer_location_id').append('<option value="' + location.id + '">' + location.name + '</option>');
                        });
                        $('#transfer_location_id').trigger('change');
                    }
                });

                $.ajax({
                    method: 'GET',
                    url: '/stock-transfer/get_transfer_store_id/' + location_id,
                    success: function (result) {
                        $('#from_store').empty().append('<option value="">Please Select</option>');
                        $.each(result, function (i, location) {
                            $('#from_store').append('<option value="' + location.id + '">' + location.name + '</option>');
                        });
                    }
                });
            });

            $('#transfer_location_id').change(function () {
                var location_id = $(this).val();
                var from_store = $('#from_store').val();
                var check_store_not = null;
                if (location_id == $('#location_id').val()) {
                    check_store_not = from_store;
                }
                $.ajax({
                    method: 'GET',
                    url: '/stock-transfer/get_transfer_store_id/' + location_id,
                    data: { check_store_not: check_store_not },
                    success: function (result) {
                        $('#to_store').empty();
                        $.each(result, function (i, location) {
                            $('#to_store').append('<option value="' + location.id + '">' + location.name + '</option>');
                        });
                        if (result.length > 0) {
                            $('#to_store_display').val(result[0].name);
                            $('#to_store').val(result[0].id);
                        }
                    }
                });
            });

            // Initial store load
            var initial_location = $('#location_id').val();
            if (initial_location) {
                $.ajax({
                    method: 'GET',
                    url: '/stock-transfer/get_transfer_store_id/' + initial_location,
                    success: function (result) {
                        $('#from_store').empty().append('<option value="">Please Select</option>');
                        $.each(result, function (i, location) {
                            $('#from_store').append('<option value="' + location.id + '">' + location.name + '</option>');
                        });
                    }
                });
            }
        });
    </script>
@endsection
@extends('layouts.app')
@section('title', ('Add stock transfer'))

@section('page-content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">@lang('Add stock transfer')</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">@lang('dashboard')</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('stock-transfers.index') }}">@lang('stock_transfers')</a>
                </li>
                <li class="breadcrumb-item active">
                    @lang('Add stock transfer')
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <form action="{{ action([\App\Http\Controllers\StockTransferController::class, 'store']) }}" method="POST"
            id="stock_transfer_form">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">@lang('Date'):*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="transaction_date" class="form-control"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}" readonly required>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">@lang('Reference No'):</label>
                                <input type="text" name="ref_no" class="form-control" value="{{ $stock_transfer_form_no }}"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">@lang('Location (From)'):*</label>
                                <select name="location_id" id="location_id" class="form-control select2" required>
                                    <option value="">@lang('please_select')</option>
                                    @foreach($business_locations as $key => $value)
                                        <option value="{{ $key }}" @if($key == $default_location) selected @endif>{{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">@lang('From Store'):*</label>
                                <select name="from_store" id="from_store" class="form-control select2" required>
                                    <option value="">@lang('please_select')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">@lang('Location(To)'):*</label>
                                <select name="transfer_location_id" id="transfer_location_id" class="form-control select2"
                                    required>
                                    <option value="">@lang('please_select')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">

                            <div class="mb-3">
                                <label class="form-label">@lang('To Store'):*</label>
                                <select name="to_store" id="to_store" class="form-control select2" required>
                                    <option value="">@lang('please_select')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title">@lang('Search Products')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-8 offset-sm-2">
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" name="search_product" id="search_product_for_srock_adjustment"
                                        class="form-control" placeholder="@lang('search product for stock adjusment')">
                                    <input type="hidden" id="module" value="stocktransfer_add">
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="product_row_index" value="0">
                    <input type="hidden" id="total_amount" name="final_total" value="0">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="stock_adjustment_product_table">
                            <thead>
                                <tr>
                                    <th class="col-sm-3 text-center">@lang('Product')</th>
                                    <th class="col-sm-2 text-center">@lang('Balance Qty')</th>
                                    <th class="col-sm-2 text-center">@lang('Units')</th>
                                    <th class="col-sm-1 text-center">@lang('Transfer Qty')</th>
                                    <th class="col-sm-2 text-center">@lang('Unit Price')</th>
                                    <th class="col-sm-3 text-center">@lang('Subtotal')</th>
                                    <th class="col-sm-2 text-center"><i class="fa fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="text-center">
                                    <td colspan="5"></td>
                                    <td>
                                        <div class="pull-right"><b>@lang('Total Amount'):</b> <span
                                                id="total_adjustment">0.00</span></div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label class="form-label">@lang('Shipping Charges'):</label>
                                <input type="text" name="shipping_charges" class="form-control input_number" value="0"
                                    placeholder="@lang('lang_v1.shipping_charges')">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="mb-3">
                                <label class="form-label">@lang('Additional Notes'):</label>
                                <textarea name="additional_notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" id="save_stock_transfer"
                                class="btn btn-primary float-end">@lang('Save')</button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

@endsection

@section('javascript')
    <script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function () {
            //Default Location logic
            var default_location_id = $('#location_id').val();
            if (default_location_id) {
                loadStores(default_location_id);
            }

            $('#location_id').change(function () {
                var location_id = $(this).val();
                loadStores(location_id);
                //Also clear to location if same? No, logic below
            });

            function loadStores(location_id) {
                if (!location_id) return;

                //Load To Locations (exclude current if needed? logic varies, usually all avail)
                $.ajax({
                    method: 'get',
                    url: '/stock-transfer/get_transfer_location/' + location_id,
                    success: function (result) {
                        $('#transfer_location_id').empty();
                        $('#transfer_location_id').append('<option value="">{{('messages.please_select')}}</option>');
                        $.each(result, function (i, location) {
                            $('#transfer_location_id').append(<option value=" + location.id + "> + location.name + </option>);
                        });

                        //Reset To Store
                        $('#to_store').empty().append('<option value="">{{('messages.please_select')}}</option>');
                    }
                });

                //Load From Stores
                $.ajax({
                    method: 'get',
                    url: '/stock-transfer/get_transfer_store_id/' + location_id,
                    success: function (result) {
                        $('#from_store').empty();
                        $('#from_store').append('<option value="">{{('messages.please_select')}}</option>');
                        $.each(result, function (i, location) {
                            $('#from_store').append(<option value=" + location.id + "> + location.name + </option>);
                        });
                        $('#search_product_for_srock_adjustment').removeAttr('disabled');
                    },
                });
            }

            $('#transfer_location_id').change(function () {
                var tr_location_id = $(this).val();
                if (!tr_location_id) return;

                $.ajax({
                    method: 'get',
                    url: '/stock-transfer/get_transfer_store_id/' + tr_location_id,
                    success: function (result) {
                        $('#to_store').empty();
                        $('#to_store').append('<option value="">{{__('messages.please_select')}}</option>');
                        $.each(result, function (i, location) {
                            $('#to_store').append(<option value=" + location.id + "> + location.name + </option>);
                        });
                    },
                });
            });

            //Initialize Select2
            $('.select2').select2();
        });

        function update_table_total() {
            var table_total = 0;
            $('table#stock_adjustment_product_table tbody tr').each(function () {
                var this_total = parseFloat(__read_number($(this).find('input.product_line_total')));
                if (this_total) {
                    table_total += this_total;
                }
            });
            $('input#total_amount').val(table_total);
            $('span#total_adjustment').text(__number_f(table_total));
        }
    </script>
@endsection
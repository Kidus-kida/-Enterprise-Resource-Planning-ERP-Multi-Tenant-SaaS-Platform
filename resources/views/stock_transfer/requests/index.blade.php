@extends('layouts.app')
@section('title', __('lang_v1.stock_transfer_requests'))

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb :title="__('lang_v1.stock_transfer_requests')">
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <button type="button" class="btn btn-primary btn-modal"
                        data-href="{{ route('stock-transfers-request.shippment') }}" data-container=".stock_transfer_modal">
                        <i class="fa fa-plus"></i> @lang('lang_v1.add_shippment')
                    </button>
                    <a href="{{ route('stock-transfers-request.shippment_list') }}" class="btn btn-info">
                        <i class="fa fa-list"></i> @lang('lang_v1.shippment_list')
                    </a>
                    <button type="button" class="btn add-btn btn-modal"
                        data-href="{{ route('stock-transfers-request.create') }}" data-container=".stock_transfer_modal">
                        <i class="fa-solid fa-plus"></i> @lang('lang_v1.add_request')
                    </button>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">@lang('report.filters')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('lang_v1.request_location'):</label>
                                    <select name="request_location" id="request_location" class="form-control select2"
                                        style="width: 100%;">
                                        <option value="">@lang('lang_v1.all')</option>
                                        @foreach($business_locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('lang_v1.request_to_location'):</label>
                                    <select name="request_to_location" id="request_to_location" class="form-control select2"
                                        style="width: 100%;">
                                        <option value="">@lang('lang_v1.all')</option>
                                        @foreach($business_locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('report.date_range'):</label>
                                    <input type="text" id="date_range" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('lang_v1.status'):</label>
                                    <select name="status" id="status" class="form-control select2" style="width: 100%;">
                                        <option value="">@lang('lang_v1.all')</option>
                                        <option value="requested">Requested</option>
                                        <option value="issued">Approved</option>
                                        <option value="transit">@lang('lang_v1.transit')</option>
                                        <option value="received">@lang('lang_v1.received')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('lang_v1.category'):</label>
                                    <select name="category_id" id="category_id" class="form-control select2"
                                        style="width: 100%;">
                                        <option value="">@lang('lang_v1.all')</option>
                                        @foreach($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">@lang('lang_v1.products'):</label>
                                    <select name="product_id" id="product_id" class="form-control select2"
                                        style="width: 100%;">
                                        <option value="">@lang('lang_v1.all')</option>
                                        @foreach($products as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stock_transfer_requests_table"
                        style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('lang_v1.request_location')</th>
                                <th>@lang('lang_v1.request_to_location')</th>
                                <th>@lang('lang_v1.rqstore')</th>
                                <th>@lang('lang_v1.product')</th>
                                <th>@lang('lang_v1.qty')</th>
                                <th>@lang('lang_v1.status')</th>
                                <th>@lang('lang_v1.received_status')</th>
                                <th>@lang('lang_v1.user')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade stock_transfer_modal" tabindex="-1" role="dialog"></div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            if ($('#date_range').length == 1) {
                $('#date_range').daterangepicker(dateRangeSettings, function (start, end) {
                    $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    stock_transfer_requests_table.ajax.reload();
                });
                $('#date_range').on('cancel.daterangepicker', function (ev, picker) {
                    $('#date_range').val('');
                    stock_transfer_requests_table.ajax.reload();
                });
            }

            stock_transfer_requests_table = $('#stock_transfer_requests_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("stock-transfers-request.index") }}',
                    data: function (d) {
                        d.request_location = $('#request_location').val();
                        d.request_to_location = $('#request_to_location').val();
                        d.category_id = $('#category_id').val();
                        d.product_id = $('#product_id').val();
                        d.status = $('#status').val();
                        if ($('#date_range').val()) {
                            var start = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'rl', name: 'rl.name' },
                    { data: 'rtl', name: 'rtl.name' },
                    { data: 'stores_name', name: 'stores.name' },
                    { data: 'product_name', name: 'products.name' },
                    { data: 'qty', name: 'qty' },
                    { data: 'status', name: 'status' },
                    { data: 'received_status', name: 'received_status' },
                    { data: 'username', name: 'users.username' },
                ],
                @include('layouts.partials.datatable_export_button')
            });

            $('#request_location, #request_to_location, #category_id, #product_id, #status').change(function () {
                stock_transfer_requests_table.ajax.reload();
            });

            $(document).on('click', 'a.delete-request', function (e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            success: function (result) {
                                if (result.success == 1) {
                                    toastr.success(result.msg);
                                    stock_transfer_requests_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
@extends('layouts.app')
@section('title', __('lang_v1.stock_transfer_requests'))

@section('page-content')
    <div class="content container-fluid">

        <!-- PAGE TITLE -->
        <h3 class="mb-3">@lang('Stock Transfer Requests')</h3>

        <!-- FILTERS -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-filter"></i> @lang('Filters')
                </h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">@lang('Request Location')</label>
                        <select id="request_location" class="form-control select2">
                            <option value="">@lang('All')</option>
                            @foreach($business_locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">@lang('Request To Location')</label>
                        <select id="request_to_location" class="form-control select2">
                            <option value="">@lang('All')</option>
                            @foreach($business_locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">@lang('Date Range')</label>
                        <input type="text" id="date_range" class="form-control" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">@lang('Category')</label>
                        <select id="category_id" class="form-control select2">
                            <option value="">@lang('All')</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">@lang('Product')</label>
                        <select id="product_id" class="form-control select2">
                            <option value="">@lang('All')</option>
                            @foreach($products as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">@lang('Status')</label>
                        <select id="status" class="form-control select2">
                            <option value="">@lang('All')</option>
                            <option value="requested">Requested</option>
                            <option value="issued">Approved</option>
                            <option value="transit">Transit</option>
                            <option value="received">Received</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <!-- TABLE HEADER (TITLE + BUTTONS) -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">@lang('All Stock Transfer Requests')</h5>

            <div class="btn-group">
                <a href="{{ route('stock-transfers-request.shippment_list') }}" class="btn btn-info">
                    <i class="fa fa-list"></i> @lang('Shipment list')
                </a>

                <button type="button" class="btn btn-primary" data-ajax-modal="true"
                    data-url="{{ route('stock-transfers-request.shippment') }}" data-title="@lang('Add Shippment')"
                    data-size="lg">
                    <i class="fa fa-plus"></i> @lang('Add shipment')
                </button>

                <button type="button" class="btn btn-primary" data-ajax-modal="true"
                    data-url="{{ route('stock-transfers-request.create') }}" data-title="@lang('Add Request')"
                    data-size="lg">
                    <i class="fa fa-plus"></i> @lang('Add Request')
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped custom-table mb-0 datatable w-100">
                        <thead>
                            <tr>
                                <th>@lang('Action')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Request Location')</th>
                                <th>@lang('Request To Location')</th>
                                <th>@lang('From store')</th>
                                <th>@lang('Product')</th>
                                <th>@lang('Qty')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Received Status')</th>
                                <th>@lang('User')</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('page-scripts')
    @vite(['resources/js/datatables.js'])
    <script type="module">
        $(document).ready(function () {

            // Initialize Select2 on all filter dropdowns
            $('.select2').select2({
                width: '100%'
            });

            // Initialize date range picker
            $('#date_range').daterangepicker({
                locale: { format: 'MM/DD/YYYY' },
                autoUpdateInput: false
            }, function (start, end) {
                $('#date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
                table.ajax.reload();
            });

            $('#date_range').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                table.ajax.reload();
            });

            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            // Initialize DataTable - using same pattern as Accounting module
            var table = $('.datatable').DataTable({
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
                            let picker = $('#date_range').data('daterangepicker');
                            d.start_date = picker.startDate.format('YYYY-MM-DD');
                            d.end_date = picker.endDate.format('YYYY-MM-DD');
                        }
                    }
                },
                columns: [
                    { data: 'action', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'rl', name: 'rl.name' },
                    { data: 'rtl', name: 'rtl.name' },
                    { data: 'stores_name', name: 'stores.name' },
                    { data: 'product_name', name: 'products.name' },
                    { data: 'qty', name: 'qty' },
                    { data: 'status', name: 'status' },
                    { data: 'received_status', name: 'received_status' },
                    { data: 'username', name: 'users.username' },
                ]
            });

            // Reload table on filter change
            $('#request_location,#request_to_location,#category_id,#product_id,#status')
                .change(function () {
                    table.ajax.reload();
                });

        });
    </script>
@endpush
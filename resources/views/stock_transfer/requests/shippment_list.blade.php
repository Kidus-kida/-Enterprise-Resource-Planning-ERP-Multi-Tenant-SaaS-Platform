@extends('layouts.app')
@section('title', 'Shippings List')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <h3 class="mb-3">@lang('Shippings List')</h3>
        <!-- /Page Header -->

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title"><i class="fa fa-filter"></i> @lang('Filters')</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">@lang('Request Location'):</label>
                        <select name="request_location" id="request_location" class="form-control select2"
                            style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($business_locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Request To Location'):</label>
                        <select name="request_to_location" id="request_to_location" class="form-control select2"
                            style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($business_locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('From Store'):</label>
                        <select name="from_store" id="from_store" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($stores as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('To Store'):</label>
                        <select name="to_store" id="to_store" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($stores as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Date Range'):</label>
                        <input type="text" id="date_range" class="form-control" readonly placeholder="@lang('')">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Category'):</label>
                        <select name="category_id" id="category_id" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Sub Category'):</label>
                        <select name="sub_category_id" id="sub_category_id" class="form-control select2"
                            style="width: 100%;">
                            <option value="">@lang('All')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Product'):</label>
                        <select name="product_id" id="product_id" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($products as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Status'):</label>
                        <select name="status" id="status" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            <option value="issued">Approved</option>
                            <option value="transit">Transit</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('Driver'):</label>
                        <select name="driver_id" id="driver_id" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            @foreach($driver as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Shippings')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="shipment_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('Action')</th>
                                <th>@lang('Select items')</th>
                                <th>@lang('Driver')</th>
                                <th>@lang('Shipping status')</th>
                                <th>@lang('Assigned date')</th>
                                <th>@lang('Delivered date')</th>
                                <th>@lang('Created by')</th>
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
            $('#date_range').daterangepicker({
                locale: { format: 'MM/DD/YYYY' },
                autoUpdateInput: false
            }, function (start, end) {
                $('#date_range').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
                shipment_table.ajax.reload();
            });

            $('#date_range').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                shipment_table.ajax.reload();
            });

            shipment_table = $('#shipment_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("stock-transfers-request.shippment_list") }}',
                    data: function (d) {
                        d.driver_id = $('#driver_id').val();
                        d.request_location = $('#request_location').val();
                        d.request_to_location = $('#request_to_location').val();
                        d.from_store = $('#from_store').val();
                        d.to_store = $('#to_store').val();
                        d.category_id = $('#category_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
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
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'select_items', name: 'select_items' },
                    { data: 'driver_name', name: 'driver_name' },
                    { data: 'shipment_status', name: 'shipment_status' },
                    { data: 'assigned_date', name: 'assigned_date' },
                    { data: 'delivered_date', name: 'delivered_date' },
                    { data: 'created_by', name: 'created_by' },
                ],
                @include('layouts.partials.datatable_export_button')
            });

            $('#driver_id, #request_location, #request_to_location, #from_store, #to_store, #category_id, #sub_category_id, #product_id, #status').change(function () {
                shipment_table.ajax.reload();
            });

            $(document).on('click', 'a.delete-shipment', function (e) {
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
                                    shipment_table.ajax.reload();
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
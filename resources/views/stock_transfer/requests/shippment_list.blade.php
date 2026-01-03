@extends('layouts.app')
@section('title', 'Shipments')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">Shipments</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('stock-transfers-request.index') }}">Stock
                        Transfer Requests</a>
                </li>
                <li class="breadcrumb-item active">
                    Shipments
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">@lang('report.filters')</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">@lang('lang_v1.driver'):</label>
                            <select name="driver_id" id="driver_id" class="form-control select2" style="width: 100%;">
                                <option value="">@lang('lang_v1.all')</option>
                                @foreach($driver as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="shipment_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('lang_v1.request_id')</th>
                                <th>@lang('lang_v1.driver')</th>
                                <th>@lang('lang_v1.shipping_status')</th>
                                <th>@lang('lang_v1.assigned_date')</th>
                                <th>@lang('lang_v1.delivered_date')</th>
                                <th>@lang('lang_v1.created_by')</th>
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
            shipment_table = $('#shipment_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("stock-transfers-request.shippment_list") }}',
                    data: function (d) {
                        d.driver_id = $('#driver_id').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'request_id', name: 'request_id' },
                    { data: 'driver_name', name: 'driver_name' },
                    { data: 'shipment_status', name: 'shipment_status' },
                    { data: 'assigned_date', name: 'assigned_date' },
                    { data: 'delivered_date', name: 'delivered_date' },
                    { data: 'created_by', name: 'created_by' },
                ],
                @include('layouts.partials.datatable_export_button')
            });

            $('#driver_id').change(function () {
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
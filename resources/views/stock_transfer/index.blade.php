@extends('layouts.app')
@section('title', __('lang_v1.stock_transfers'))

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb :title="__('lang_v1.stock_transfers')">
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    @can('purchase.create')
                        <a href="{{route('stock-transfers.create')}}" class="btn add-btn">
                            <i class="fa-solid fa-plus"></i> @lang('messages.add')
                        </a>
                    @endcan
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stock_transfer_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('messages.date')</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.location_from')</th>
                                <th>@lang('lang_v1.location_to')</th>
                                <th>@lang('lang_v1.from_store')</th>
                                <th>@lang('lang_v1.to_store')</th>
                                <th>@lang('lang_v1.shipping_charges')</th>
                                <th>@lang('stock_adjustment.total_amount')</th>
                                <th>@lang('purchase.additional_notes')</th>
                                <th>@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <section id="receipt_section" class="print_section"></section>

@endsection

@section('javascript')
    <script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            stock_transfer_table = $('#stock_transfer_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/stock-transfers',
                columnDefs: [
                    {
                        targets: [6, 7, 9],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'ref_no', name: 'ref_no' },
                    { data: 'location_from', name: 'l1.name' },
                    { data: 'location_to', name: 'l2.name' },
                    { data: 'from_store', name: 'from_store' },
                    { data: 'to_store', name: 'to_store' },
                    { data: 'shipping_charges', name: 'shipping_charges' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'additional_notes', name: 'additional_notes' },
                    { data: 'action', name: 'action' },
                ],
                @include('layouts.partials.datatable_export_button')
                        fnDrawCallback: function (oSettings) {
                    __currency_convert_recursively($('#stock_transfer_table'));
                },
            });
        });
    </script>
@endsection
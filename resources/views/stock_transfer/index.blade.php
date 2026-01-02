@extends('layouts.app')
@section('title', ('Stock_transfers'))

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb :title="('All Stock transfers')">
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                @can('purchase.create')
                    <a href="{{ route('stock-transfers.create') }}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> @lang('Add stock transfer')
                    </a>
                @endcan
            </div>
        </x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="stock_transfer_table" style="width:100%">
                    <thead>
                        <tr>
                            <th>@lang('Date')</th>
                            <th>@lang('Reference No')</th>
                            <th>@lang('Location (from)')</th>
                            <th>@lang('Location (to)')</th>
                            <th>@lang('From store')</th>
                            <th>@lang('To store')</th>
                            <th>@lang('Shipping Charges')</th>
                            <th>@lang('Total Amount')</th>
                            <th>@lang('Notes')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<section id="receipt_section" class="print_section"></section>
@endsection


@section('javascript')
<script src="{{ asset('js/stock_transfer.js?v=' . $asset_v) }}"></script>

<script>
$(document).ready(function () {

    let stock_transfer_table = $('#stock_transfer_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/stock-transfers') }}",

        // ✅ SEARCH, SHOW, PAGINATION
        paging: true,
        searching: true,
        lengthChange: true,
        info: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        columnDefs: [
            {
                targets: [6, 7, 8, 9],
                orderable: false,
                searchable: false
            }
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
            { data: 'action', name: 'action' }
        ],

        fnDrawCallback: function () {
            __currency_convert_recursively($('#stock_transfer_table'));
        }
    });

});
</script>
@endsection

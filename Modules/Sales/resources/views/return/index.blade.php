@extends('layouts.app')
@section('page-content')

<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Sales Returns') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('sales.index') }}">{{ __('Sales') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Sales Returns') }}
            </li>
        </ul>
    </x-breadcrumb>

    @if(session('status'))
        <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ session('status.msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title">@lang('All Sales Returns')</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="sales_return_datatable">
                    <thead>
                        <tr>
                            <th>@lang('Date')</th>
                            <th>@lang('Invoice No')</th>
                            <th>@lang('Parent Sale')</th>
                            <th>@lang('Location')</th>
                            <th>@lang('Customer')</th>
                            <th>@lang('Payment Status')</th>
                            <th>@lang('Grand Total')</th>
                            <th>@lang('Payment Due')</th>
                            <th>@lang('Action')</th>
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
<script>
    window.addEventListener('load', function() {
        init_sales_return_table();
    });

    function init_sales_return_table() {
        if ($('#sales_return_datatable').length) {
            if (typeof $.fn.DataTable === 'undefined') {
                setTimeout(init_sales_return_table, 100);
                return;
            }

            var sales_return_table = $('#sales_return_datatable').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[0, 'desc']],
                ajax: "{{ route('sales-return.index') }}",
                columnDefs: [ {
                    "targets": [7, 8],
                    "orderable": false,
                    "searchable": false
                } ],
                columns: [
                    { data: 'transaction_date', name: 'transaction_date'  },
                    { data: 'invoice_no', name: 'transactions.invoice_no'},
                    { data: 'parent_sale', name: 'T.invoice_no'},
                    { data: 'location_name', name: 'BS.name'},
                    { data: 'name', name: 'contacts.name'},
                    { data: 'payment_status', name: 'transactions.payment_status'},
                    { data: 'final_total', name: 'transactions.final_total'},
                    { data: 'payment_due', name: 'payment_due', orderable: false, searchable: false},
                    { data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });
        }
    }
</script>
@endpush

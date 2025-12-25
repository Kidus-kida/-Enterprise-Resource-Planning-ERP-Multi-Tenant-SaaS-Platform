@extends('layouts.app')
@section('page-content')

<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Purchase Returns') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('purchase.index') }}">{{ __('Purchases') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Purchase Returns') }}
            </li>
        </ul>
    </x-breadcrumb>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('All Purchase Returns')</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered datatable" id="purchase_return_datatable">
                    <thead>
                        <tr>
                            <th>@lang('Date')</th>
                            <th>@lang('Ref No')</th>
                            <th>@lang('Parent Purchase')</th>
                            <th>@lang('Location')</th>
                            <th>@lang('Supplier')</th>
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

@push('page-script')
<script>
    $(document).ready( function(){
        //Purchase return table
        var purchase_return_table = $('#purchase_return_datatable').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[0, 'desc']],
            ajax: '{{ route('purchase-return.index') }}',
            columnDefs: [ {
                "targets": [7, 8],
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'ref_no', name: 'ref_no'},
                { data: 'parent_purchase', name: 'T.ref_no'},
                { data: 'location_name', name: 'BS.name'},
                { data: 'name', name: 'contacts.name'},
                { data: 'payment_status', name: 'payment_status'},
                { data: 'final_total', name: 'final_total'},
                { data: 'payment_due', name: 'payment_due'},
                { data: 'action', name: 'action'}
            ]
        });
    });
</script>
@endpush

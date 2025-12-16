@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Customer Payments') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Customer Payments') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="javascript:void(0)" data-url="{{ route('customer-payments.create') }}" class="btn add-btn"
                        data-ajax-modal="true" data-size="md" data-title="Add Payment">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Payment') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>

        <!-- Search Filter -->
        <div class="row filter-row">
            <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus">
                     <div class="cal-icon">
                        <input class="form-control floating datepicker" type="text" id="filter_start_date" placeholder="Start Date">
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                 <div class="input-block mb-3 form-focus">
                    <div class="cal-icon">
                        <input class="form-control floating datepicker" type="text" id="filter_end_date" placeholder="End Date">
                    </div>
                 </div>
            </div>
             <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus select-focus">
                    <select class="select floating" id="filter_customer">
                        <option value=""> -- Select Customer -- </option>
                        @foreach(Modules\Contacts\Models\Contact::where('type', 'customer')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <label class="focus-label">Customer</label>
                </div>
            </div>
             <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus select-focus">
                    <select class="select floating" id="filter_method">
                        <option value=""> -- Select Method -- </option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                    <label class="focus-label">Method</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="d-grid">
                    <a href="javascript:void(0)" class="btn btn-success" id="btn-filter"> Search </a>
                </div>
            </div>
        </div>
        <!-- /Search Filter -->

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush

@push('scripts')
<script>
    $(document).ready(function(){
        if($('.datepicker').length > 0) {
            $('.datepicker').datetimepicker({
                format: 'YYYY-MM-DD',
                icons: {
                    up: "fa fa-angle-up",
                    down: "fa fa-angle-down",
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                }
            });
        }
        if($('.select').length > 0) {
            $('.select').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            });
        }

        $('#btn-filter').on('click', function(){
            var table = $('#customer-payment-table').DataTable();
            table.on('preXhr.dt', function ( e, settings, data ) {
                data.start_date = $('#filter_start_date').val();
                data.end_date = $('#filter_end_date').val();
                data.contact_id = $('#filter_customer').val();
                data.method = $('#filter_method').val();
            });
            table.ajax.reload();
        });
    });
</script>

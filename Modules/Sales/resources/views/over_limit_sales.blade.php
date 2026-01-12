@extends('layouts.app')

@push('page-styles')
    <!-- Page Css -->
    <!-- /Page Css -->
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Over Limit Sales') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="#">{{ __('Sales') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Over Limit Sales') }}</li>
            </ul>
        </x-breadcrumb>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title">{{ __('Filters') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sell_list_filter_date_range">{{ __('Date Range') }}</label>
                            <input type="text" id="sell_list_filter_date_range" class="form-control" readonly placeholder="{{ __('Select date range') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sell_list_filter_location_id">{{ __('Business Location') }}</label>
                            <select id="sell_list_filter_location_id" class="form-control select2">
                                <option value="">{{ __('All') }}</option>
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sell_list_filter_customer_id">{{ __('Customer') }}</label>
                            <select id="sell_list_filter_customer_id" class="form-control select2">
                                <option value="">{{ __('All') }}</option>
                                @foreach($customers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sell_list_filter_payment_status">{{ __('Payment Status') }}</label>
                            <select id="sell_list_filter_payment_status" class="form-control select2">
                                <option value="">{{ __('All') }}</option>
                                <option value="paid">{{ __('Paid') }}</option>
                                <option value="due">{{ __('Due') }}</option>
                                <option value="partial">{{ __('Partial') }}</option>
                                <option value="overdue">{{ __('Overdue') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="approved_users">{{ __('Approved Users') }}</label>
                            <select id="approved_users" class="form-control select2">
                                <option value="">{{ __('All') }}</option>
                                @foreach($approved_users as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="invoice_no">{{ __('Invoice No') }}</label>
                            <select id="invoice_no" class="form-control select2">
                                <option value="">{{ __('All') }}</option>
                                @foreach($invoice_nos as $invoice)
                                    <option value="{{ $invoice }}">{{ $invoice }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="over_limit_sell_table">
                        <thead>
                            <tr>
                                <th>{{ __('Action') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Customer Name') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Total Paid') }}</th>
                                <th>{{ __('Limit') }}</th>
                                <th>{{ __('Over Limit Amount') }}</th>
                                <th>{{ __('Approved By') }}</th>
                                <th>{{ __('Requested By') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        // Initialize date range picker if available
        if (typeof dateRangeSettings !== 'undefined' && $.fn.daterangepicker) {
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#sell_list_filter_date_range').val(start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD'));
                    table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                table.ajax.reload();
            });
        }

        // Initialize DataTable
        var table = $('#over_limit_sell_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales.over_limit_sales') }}",
                data: function(d) {
                    if ($('#sell_list_filter_date_range').val()) {
                        var dates = $('#sell_list_filter_date_range').val().split(' ~ ');
                        d.start_date = dates[0];
                        d.end_date = dates[1];
                    }
                    d.location_id = $('#sell_list_filter_location_id').val();
                    d.customer_id = $('#sell_list_filter_customer_id').val();
                    d.payment_status = $('#sell_list_filter_payment_status').val();
                    d.invoice_no = $('#invoice_no').val();
                    d.approved_user = $('#approved_users').val();
                    d.is_direct_sale = 1;
                }
            },
            columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'name', name: 'contacts.name' },
                { data: 'business_location', name: 'bl.name' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'payment_methods', orderable: false, searchable: false },
                { data: 'final_total', name: 'final_total' },
                { data: 'total_paid', name: 'total_paid', searchable: false },
                { data: 'customer_limit', name: 'customer_limit', searchable: false },
                { data: 'over_limit_amount', name: 'over_limit_amount', searchable: false },
                { data: 'approved_by', name: 'approved_by' },
                { data: 'requested_by', name: 'requested_by' }
            ],
            order: [[1, 'desc']]
        });

        // Filter change handlers
        $('#sell_list_filter_location_id, #approved_users, #invoice_no, #sell_list_filter_customer_id, #sell_list_filter_payment_status').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush

@extends('layouts.app')

@push('page-styles')
    <!-- Page Css -->
    <!-- /Page Css -->
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Subscriptions') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="#">{{ __('Sales') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Subscriptions') }}</li>
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
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="sell_table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Customer Name') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Interval') }}</th>
                                <th>{{ __('Repetitions') }}</th>
                                <th>{{ __('Generated Invoices') }}</th>
                                <th>{{ __('Last Generated') }}</th>
                                <th>{{ __('Upcoming Invoice') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
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
        var table = $('#sell_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales.subscriptions.index') }}",
                data: function(d) {
                    if ($('#sell_list_filter_date_range').val()) {
                        var dates = $('#sell_list_filter_date_range').val().split(' ~ ');
                        d.start_date = dates[0];
                        d.end_date = dates[1];
                    }
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'name', name: 'contacts.name' },
                { data: 'business_location', name: 'bl.name' },
                { data: 'recur_interval', name: 'recur_interval' },
                { data: 'recur_repetitions', name: 'recur_repetitions' },
                { data: 'subscription_invoices', searchable: false, orderable: false },
                { data: 'last_generated', searchable: false, orderable: false },
                { data: 'upcoming_invoice', searchable: false, orderable: false },
                { data: 'action', name: 'action' }
            ],
            order: [[0, 'desc']]
        });

        $(document).on('click', 'a.toggle_recurring_invoice', function(e){
            e.preventDefault();
            $.ajax({
                method: "GET",
                url: $(this).attr('href'),
                dataType: "json",
                success: function(data){
                    if(data.success == true){   
                        toastr.success(data.msg);
                        table.ajax.reload();
                    } else {
                        toastr.error(data.msg);
                    }
                }
            });
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Post-Dated Cheques') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Post-Dated Cheques') }}</li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('post-dated-cheques.create') }}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> {{ __('Add PDC') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success-light">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Received - Pending') }}</h6>
                        <h4 class="mb-0" id="received_pending_amount">0.00</h4>
                        <small class="text-muted" id="received_pending_count">0 {{ __('cheques') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info-light">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Received - Realized') }}</h6>
                        <h4 class="mb-0" id="received_realized_amount">0.00</h4>
                        <small class="text-muted" id="received_realized_count">0 {{ __('cheques') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning-light">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Issued - Pending') }}</h6>
                        <h4 class="mb-0" id="issued_pending_amount">0.00</h4>
                        <small class="text-muted" id="issued_pending_count">0 {{ __('cheques') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary-light">
                    <div class="card-body">
                        <h6 class="text-muted">{{ __('Issued - Realized') }}</h6>
                        <h4 class="mb-0" id="issued_realized_amount">0.00</h4>
                        <small class="text-muted" id="issued_realized_count">0 {{ __('cheques') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>{{ __('PDC Type') }}</label>
                                <select id="pdc_type_filter" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="received">{{ __('Received') }}</option>
                                    <option value="issued">{{ __('Issued') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('Status') }}</label>
                                <select id="status_filter" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="pending" selected>{{ __('Pending') }}</option>
                                    <option value="realized">{{ __('Realized') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('From Date') }}</label>
                                <input type="date" id="from_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('To Date') }}</label>
                                <input type="date" id="to_date" class="form-control">
                            </div>
                        </div>

                        <!-- PDC Table -->
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Cheque Number') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Party') }}</th>
                                        <th>{{ __('Cheque Date') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th>{{ __('Bank Account') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
@vite(["resources/js/datatables.js"])
<script type="module">
    function loadSummary() {
        $.ajax({
            url: "{{ route('pdc.filters') }}",
            data: {
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            },
            success: function(response) {
                $('#received_pending_amount').text(response.received_pending_amount);
                $('#received_pending_count').text(response.received_pending_count + ' {{ __("cheques") }}');
                $('#received_realized_amount').text(response.received_realized_amount);
                $('#received_realized_count').text(response.received_realized_count + ' {{ __("cheques") }}');
                $('#issued_pending_amount').text(response.issued_pending_amount);
                $('#issued_pending_count').text(response.issued_pending_count + ' {{ __("cheques") }}');
                $('#issued_realized_amount').text(response.issued_realized_amount);
                $('#issued_realized_count').text(response.issued_realized_count + ' {{ __("cheques") }}');
            }
        });
    }

    $(document).ready(function(){
        loadSummary();
        
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
        
        var table = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: "{{route('post-dated-cheques.index')}}",
                data: function(d) {
                    d.pdc_type = $('#pdc_type_filter').val();
                    d.status = $('#status_filter').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'cheque_number', name: 'cheque_number'},
                {data: 'type', name: 'type'},
                {data: 'party', name: 'party'},
                {data: 'cheque_date', name: 'cheque_date'},
                {data: 'due_date', name: 'due_date'},
                {data: 'amount', name: 'amount', className: 'text-end'},
                {data: 'bank_account', name: 'bank_account'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Reload on filter change
        $('#pdc_type_filter, #status_filter, #from_date, #to_date').change(function(){
            table.draw();
            loadSummary();
        });
    });
</script>
@endpush

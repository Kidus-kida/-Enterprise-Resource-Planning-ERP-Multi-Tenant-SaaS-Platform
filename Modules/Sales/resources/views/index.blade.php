@extends('layouts.app')

@push('page-styles')
    <!-- Page Css -->
    <!-- /Page Css -->
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Sales List') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Sales') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row mb-4">
            <div class="col-md-12 text-end">
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add Sales') }}
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status_filter">{{ __('Status') }}</label>
                            <select id="status_filter" class="form-control">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>{{ __('All') }}</option>
                                <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>{{ __('Final') }}</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="proforma" {{ request('status') == 'proforma' ? 'selected' : '' }}>{{ __('Proforma') }}</option>
                                <option value="order" {{ request('status') == 'order' ? 'selected' : '' }}>{{ __('Order') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                             <label>&nbsp;</label><br>
                             <button type="button" class="btn btn-primary" id="filter_btn">{{ __('Filter') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="sales_table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Paid Amount') }}</th>
                                <th>{{ __('Due Amount') }}</th>
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
        var sales_table = $('#sales_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales.list') }}",
                data: function(d) {
                    d.is_pos = 0;
                    d.is_quotation = 0;
                    d.status = $('#status_filter').val();
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'location_name', name: 'location_name' },
                { data: 'status', name: 'status' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'total_paid', name: 'total_paid' },
                { data: 'total_due', name: 'total_due' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });

        $('#filter_btn').on('click', function() {
            sales_table.ajax.reload();
        });
    });
</script>
@endpush

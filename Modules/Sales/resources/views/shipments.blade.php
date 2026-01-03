@extends('layouts.app')

@push('page-styles')
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Shipments') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('Sales') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Shipments') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="card">
            <div class="card-body border-bottom">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('shipping_status',  __('Shipping Status') . ':') !!}
                            {!! Form::select('shipping_status', $shipping_statuses, null, ['class' => 'form-control', 'id' => 'shipping_status_filter', 'placeholder' => __('messages.all')]); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="shipments_table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Shipping Status') }}</th>
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
@endsection

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        var shipments_table = $('#shipments_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales.list') }}",
                data: function(d) {
                    d.only_shipments = true;
                    d.shipping_status = $('#shipping_status_filter').val();
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'location_name', name: 'location_name' },
                { data: 'shipping_status', name: 'shipping_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });

        $(document).on('change', '#shipping_status_filter', function() {
            shipments_table.ajax.reload();
        });

        $(document).on('click', 'a.edit_shipping', function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).data('href'),
                dataType: 'html',
                success: function(result) {
                    $('.view_modal')
                        .html(result)
                        .modal('show');
                },
            });
        });

        $(document).on('submit', 'form#edit_shipping_form', function(e) {
            e.preventDefault();
            $(this)
                .find('button[type="submit"]')
                .attr('disabled', true);
            var data = $(this).serialize();

            $.ajax({
                method: 'PUT',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == 1) {
                        $('.view_modal').modal('hide');
                        shipments_table.ajax.reload();
                    } else {
                        alert(result.msg);
                        $(this)
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    }
                },
            });
        });
    });
</script>
@endpush

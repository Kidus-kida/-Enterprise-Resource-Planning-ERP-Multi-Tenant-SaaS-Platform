@extends('layouts.app')

@push('page-styles')
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('lang_v1.shipments') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
                <li class="breadcrumb-item active">{{ __('lang_v1.shipments') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="card">
            <div class="card-body border-bottom">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('shipping_status',  __('lang_v1.shipping_status') . ':') !!}
                            {!! Form::select('shipping_status', $shipping_statuses, null, ['class' => 'form-control', 'id' => 'shipping_status_filter', 'placeholder' => __('messages.all')]); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="purchase_shipments_table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Reference No') }}</th>
                                <th>{{ __('Supplier') }}</th>
                                <th>{{ __('lang_v1.shipping_status') }}</th>
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
        var purchase_shipments_table = $('#purchase_shipments_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('purchase.list') }}",
                data: function(d) {
                    d.only_shipments = true;
                    d.shipping_status = $('#shipping_status_filter').val();
                }
            },
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'ref_no', name: 'ref_no' },
                { data: 'supplier_name', name: 'supplier_name' },
                { data: 'shipping_status', name: 'shipping_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });

        $(document).on('change', '#shipping_status_filter', function() {
            purchase_shipments_table.ajax.reload();
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
                        purchase_shipments_table.ajax.reload();
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

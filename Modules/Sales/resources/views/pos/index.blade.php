@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('POS Sales List') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('POS Sales') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row mb-4">
            <div class="col-md-12 text-end">
                <a href="{{ route('sales.pos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('POS') }}
                </a>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0" id="pos_table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Invoice No') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Location') }}</th>
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

    <!-- Hidden Iframe for printing -->
    <iframe id="receipt_section" style="display:none;"></iframe>

@endsection

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        $('#pos_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('sales.pos.list') }}",
            columns: [
                { data: 'transaction_date', name: 'transaction_date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'location_name', name: 'location_name' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'total_paid', name: 'total_paid' },
                { data: 'total_due', name: 'total_due' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
    });

    window.pos_print_invoice = function(transaction_id) {
        $.ajax({
            url: "{{ url('sales/pos/print_invoice') }}/" + transaction_id,
            dataType: "json",
            success: function(result) {
                if (result.success == 1) {
                    pos_print(result.receipt);
                } else {
                    alert(result.msg);
                }
            }
        });
    }

    function pos_print(receipt) {
        if (receipt.print_type == 'browser') {
            const iframe = document.getElementById('receipt_section');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(receipt.html_content);
            doc.close();
            setTimeout(() => {
                iframe.contentWindow.print();
            }, 500);
        }
    }
</script>
@endpush

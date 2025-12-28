<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="fa fa-filter me-2"></i> Filters
        </h5>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="text" id="cheques_date_range" class="form-control" placeholder="Select date range" autocomplete="off">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select id="cheques_customer_filter" class="form-select select2">
                    <option value="">All</option>
                    @if (isset($customers))
                        @foreach ($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa fa-table me-2"></i> Cheques Report
        </h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle w-100" id="cheques_table">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cheque Number</th>
                        <th>Amount</th>
                        <th>Cheque Date</th>
                        <th>Bank</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Date Range Picker CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@push('page-scripts')
<script type="module">
$(document).ready(function() {

    $('.select2').select2();

    // Initialize DataTable
    const table = $('#cheques_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('account.cheques-list') }}",
            data: function(d) {
                d.date_range = $('#cheques_date_range').val();
                d.customer_id = $('#cheques_customer_filter').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'customer' },
            { data: 'cheque_number' },
            { data: 'amount' },
            { data: 'cheque_date' },
            { data: 'bank' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // Reload table on filter change
    $('#cheques_customer_filter, #cheques_date_range').on('change', function() {
        table.ajax.reload();
    });

    // Initialize Date Range Picker
    function initChequesDateRangePicker() {
         if ($('#cheques_date_range').data('daterangepicker')) return;

         $('#cheques_date_range').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'This Week': [moment().startOf('week'), moment().endOf('week')],
                'Last Week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            },
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        });

        $('#cheques_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
            table.ajax.reload();
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.ajax.reload();
        });
    }

    if ($.fn.daterangepicker) {
        initChequesDateRangePicker();
    } else {
        $(document).on('daterangepicker:ready', initChequesDateRangePicker);
    }

});
</script>
@endpush

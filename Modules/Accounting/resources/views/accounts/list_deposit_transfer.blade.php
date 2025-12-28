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
                    <input type="date" id="date_range" class="form-control" placeholder="Select date range"
                        autocomplete="on">
                </div>
            </div>


            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select id="type_filter" class="form-select select2">
                    <option value="">All</option>
                    <option value="deposit">Deposit</option>
                    <option value="fund_transfer">Transfer</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">From Account</label>
                <select id="from_account_filter" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($accounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">To Account</label>
                <select id="to_account_filter" class="form-select select2">
                    <option value="">All</option>
                    @foreach ($accounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa fa-table me-2"></i> Deposit & Transfer Report
        </h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle w-100" id="deposit_transfer_table">
                <thead class="table-light">
                    <tr>
                        <th>Action</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>From Account</th>
                        <th>To Account</th>
                        <th>Cheque No</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        // Initialize DataTable
        $('.select2').select2();

        const table = $('#deposit_transfer_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('deposit-transfers.index') }}",
                data: function(d) {
                    d.date_range = $('#date_range').val();
                    d.type = $('#type_filter').val();
                    d.from_account = $('#from_account_filter').val();
                    d.to_account = $('#to_account_filter').val();
                }
            },
            columns: [{
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'date'
                },
                {
                    data: 'name'
                },
                {
                    data: 'type'
                },
                {
                    data: 'amount'
                },
                {
                    data: 'from_account',
                    orderable: false
                },
                {
                    data: 'to_account',
                    orderable: false
                },
                {
                    data: 'cheque_number'
                },
                {
                    data: 'user',
                    orderable: false
                }
            ]
        });

        $('#type_filter, #from_account_filter, #to_account_filter, #date_range')
            .on('change', function() {
                table.ajax.reload();
            });

        // Initialize DateRangePicker when ready
        function initDateRangePicker() {
            if ($('#date_range').data('daterangepicker')) return; // Already initialized

            $('#date_range').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
                table.ajax.reload(); // Reload table on apply
            }).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                table.ajax.reload();
            });
        }

        if ($.fn.daterangepicker) {
            initDateRangePicker();
        } else {
            $(document).on('daterangepicker:ready', initDateRangePicker);
        }
    });
</script>
@endpush

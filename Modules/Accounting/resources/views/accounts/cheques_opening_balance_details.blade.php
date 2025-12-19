<div class="row">
    <div class="col-md-12">
        <div class="col-md-3">
            <div class="form-group">
                <label for="cheques_ob_details_date_range">{{ __('lang_v1.date_range') }}:</label>
                <input type="text" name="cheques_ob_details_date_range" id="cheques_ob_details_date_range"
                    class="form-control" style="width: 100%;">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cheques_ob_details_customer">{{ __('lang_v1.customer') }}:</label>
                <select name="cheques_ob_details_customer" id="cheques_ob_details_customer" class="form-control select2"
                    style="width: 100%;">
                    <option value="">{{ __('lang_v1.all') }}</option>
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
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="cheques_ob_details_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>date</th>
                        <th>customer</th>
                        <th>cheque_number</th>
                        <th>amount</th>
                        <th>cheque_date</th>
                        <th>bank</th>
                        <th>action</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="module">
    $(document).ready(function() {
        $('#cheques_ob_details_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('account.cheques-list') }}",
                data: function(d) {
                    d.start_date = null;
                    d.end_date = null;
                    d.customer_id = $('#cheques_ob_details_customer').val();
                }
            },
            columns: [{
                    data: 'date',
                    name: 'operation_date'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'cheque_number',
                    name: 'cheque_number'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'cheque_date',
                    name: 'cheque_date'
                },
                {
                    data: 'bank',
                    name: 'bank'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#cheques_ob_details_customer').change(function() {
            $('#cheques_ob_details_table').DataTable().ajax.reload();
        });
    });
</script>

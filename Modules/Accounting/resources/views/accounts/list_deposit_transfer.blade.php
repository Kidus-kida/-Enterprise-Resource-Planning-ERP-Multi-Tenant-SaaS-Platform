<div class="row">
    <div class="col-md-12">
        <div class="col-md-3">
            <div class="form-group">
                <label for="list_deposit_transfer_date_range">{{ __('lang_v1.date_range') . ':' }}</label>
                <input type="text" id="list_deposit_transfer_date_range" name="list_deposit_transfer_date_range" class="form-control" style="width: 100%;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="list_deposit_transfer_type">{{ __('lang_v1.type') . ':' }}</label>
                <select id="list_deposit_transfer_type" name="list_deposit_transfer_type" class="form-control select2" style="width: 100%;">
                    <option value="">{{ __('lang_v1.all') }}</option>
                    <option value="deposit">{{ __('Deposit') }}</option>
                    <option value="fund_transfer">{{ __('Transfer') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="from_account_id">{{ __('lang_v1.from_account') . ':' }}</label>
                <select id="from_account_id" name="from_account_id" class="form-control select2" style="width: 100%;">
                    <option value="">{{ __('lang_v1.all') }}</option>
                    @foreach($accounts ?? [] as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="to_account_id">{{ __('lang_v1.to_account') . ':' }}</label>
                <select id="to_account_id" name="to_account_id" class="form-control select2" style="width: 100%;">
                    <option value="">{{ __('lang_v1.all') }}</option>
                    @foreach($accounts ?? [] as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="list_deposit_transfer_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>@lang('lang_v1.action')</th>
                        <th>@lang('lang_v1.date')</th>
                        <th>@lang('lang_v1.name')</th>
                        <th>@lang('lang_v1.type')</th>
                        <th>@lang('lang_v1.amount')</th>
                        <th>@lang('lang_v1.from_account')</th>
                        <th>@lang('lang_v1.to_account')</th>
                        <th>@lang('lang_v1.cheque_number')</th>
                        <th>@lang('lang_v1.user')</th>
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
        $('#list_deposit_transfer_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('deposit-transfers.index') }}",
                data: function(d) {
                    d.start_date = null; // Add date filter logic if needed
                    d.end_date = null;
                    d.type = $('#list_deposit_transfer_type').val();
                    d.from_account_id = $('#from_account_id').val();
                    d.to_account_id = $('#to_account_id').val();
                }
            },
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'date',
                    name: 'operation_date'
                },
                {
                    data: 'name',
                    name: 'account.name'
                }, // Adjust based on relation
                {
                    data: 'type',
                    name: 'sub_type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'from_account',
                    name: 'from_account',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'to_account',
                    name: 'to_account',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'cheque_number',
                    name: 'cheque_number'
                },
                {
                    data: 'user',
                    name: 'user',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#list_deposit_transfer_type, #from_account_id, #to_account_id').change(function() {
            $('#list_deposit_transfer_table').DataTable().ajax.reload();
        });
    });
</script>

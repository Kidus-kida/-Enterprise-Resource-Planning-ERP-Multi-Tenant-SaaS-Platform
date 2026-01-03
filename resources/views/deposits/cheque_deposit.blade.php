<div class="modal-dialog" role="document" style="width: 60%">
  <div class="modal-content">
    <form method="POST" action="{{ route('deposits.postChequeDeposit') }}" id="deposit_form"
      enctype="multipart/form-data">
      @csrf
      <div class="modal-header text-center">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang('deposits.cheque_deposit')</h4>
      </div>
      <div class="modal-body">
        <div class="col-md-4">
          <div class="form-group" style="margin-top: 28px;">
            <strong>@lang('deposits.selected_account'):</strong>
            {{ $account->name }}
            <input type="hidden" name="account_id" value="{{ $account->id }}">
          </div>
        </div>
        <!-- Balance field removed intentionally -->
        <div class="col-md-4">
          <div class="form-group">
            <label for="operation_date">{{ __('deposits.transaction_date') }}:*</label>
            <input type="text" name="operation_date" id="transaction_date"
              class="form-control pull-right transaction_date" required
              placeholder="{{ __('deposits.transaction_date') }}">
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="transaction_date_range_cheque_deposit">{{ __('report.date_range') }}:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="transaction_date_range_cheque_deposit"
                  id="transaction_date_range_cheque_deposit" class="form-control" readonly
                  placeholder="{{ __('report.date_range') }}">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="transaction_date_range_cheque_deposit_created">{{ __('report.created_on') }}:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="transaction_date_range_cheque_deposit_created"
                  id="transaction_date_range_cheque_deposit_created" class="form-control" readonly
                  placeholder="{{ __('report.created_on') }}">
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            <label for="cheque_customer_cheque_no">{{ __('lang_v1.customer_cheque_number') }}:</label>
            <select name="customer_cheque_no" id="cheque_customer_cheque_no" class="form-control select2"
              style="width: 100%;" placeholder="{{ __('lang_v1.all') }}">
              <!-- options populated via AJAX -->
            </select>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="cheque_customer_amount">{{ __('lang_v1.amount') }}:</label>
            <select name="customer_amount" id="cheque_customer_amount" class="form-control select2" style="width: 100%;"
              placeholder="{{ __('lang_v1.all') }}">
              <!-- options populated via AJAX -->
            </select>
          </div>
        </div>
        <div class="clearfix"></div>
        <table class="table table-bordered table-striped" id="cheque_list_table">
          <thead>
            <tr>
              <th>@lang('deposits.select')</th>
              <th>@lang('lang_v1.name')</th>
              <th>@lang('deposits.cheque_no')</th>
              <th>@lang('deposits.cheque_date')</th>
              <th>@lang('deposits.bank')</th>
              <th>@lang('deposits.amount')</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        <div class="clearfix"></div>
        <div class="col-md-12 text-center">
          <div class="checkbox">
            <label>
              <input type="checkbox" name="encash" value="1" class="input-icheck" id="encash">
              {{ __('deposits.encash') }}
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="from_account">{{ __('deposits.deposit_to') }}:</label>
          <select name="from_account" id="from_account" class="form-control select2" required>
            @foreach($to_accounts as $key => $value)
              <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="note">{{ __('brand.note') }}</label>
          <textarea name="note" id="note" class="form-control" placeholder="{{ __('brand.note') }}" rows="4"></textarea>
        </div>
        <div class="form-group">
          <label for="attachment">{{ __('lang_v1.add_image_document') }}</label>
          <input type="file" name="attachment" id="attachment" class="form-control" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary submit_btn">@lang('messages.submit')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
    $('#cheque_customer_cheque_no, #cheque_customer_amount').change(function () {
      get_cheques_list();
    });
  });
  // Populate cheque and amount selects
  $.ajax({
    method: 'get',
    url: '/customer-payment-information/all/cheque_no',
    success: function (result) {
      var select = $('#cheque_customer_cheque_no');
      select.empty();
      select.append($('<option>').val('').text('@lang('lang_v1.all')'));
      $.each(result.data, function (i, v) {
        select.append($('<option>').val(v).text(v));
      });
    }
  });
  $.ajax({
    method: 'get',
    url: '/customer-payment-information/all/amount',
    success: function (result) {
      var select = $('#cheque_customer_amount');
      select.empty();
      select.append($('<option>').val('').text('@lang('lang_v1.all')'));
      $.each(result.data, function (i, v) {
        select.append($('<option>').val(v).text(v));
      });
    }
  });
  $('#transaction_date').datetimepicker({
    format: moment_date_format + ' ' + moment_time_format
  });
  $('#encash').change(function () {
    $('#from_account').prop('disabled', $(this).is(':checked'));
  });
  $('#transaction_date_range_cheque_deposit').daterangepicker(dateRangeSettings, function (start, end) {
    $('#transaction_date_range_cheque_deposit').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
    get_cheques_list();
  }).data('daterangepicker').setStartDate(moment().startOf('month')).setEndDate(moment().endOf('month')).trigger('change');
  $('#transaction_date_range_cheque_deposit_created').daterangepicker(dateRangeSettings, function (start, end) {
    $('#transaction_date_range_cheque_deposit_created').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
    get_cheques_list();
  }).data('daterangepicker').setStartDate(moment().startOf('month')).setEndDate(moment().endOf('month')).trigger('change');
  $('.select2').select2();
</script>
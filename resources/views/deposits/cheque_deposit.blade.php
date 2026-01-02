<div class="modal-dialog modal-xl" role="document" style="width: 90%;">
  <div class="modal-content">
    <form method="POST" action="{{ route('deposits.postChequeDeposit') }}" id="deposit_form"
      enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Cheque Deposit</h4>
      </div>
      <div class="modal-body">
        <div class="well"
          style="background-color: #f5f5f5; border: 1px solid #e3e3e3; padding: 10px; margin-bottom: 20px;">
          <strong>Selected Account:</strong> {{ $account->name }}
          <input type="hidden" name="account_id" value="{{ $account->id }}">
        </div>

        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label for="operation_date">Transaction Date:*</label>
              <input type="text" name="operation_date" id="transaction_date" class="form-control transaction_date"
                required placeholder="Transaction Date">
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="transaction_date_range_cheque_deposit">Date Range:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="transaction_date_range_cheque_deposit"
                  id="transaction_date_range_cheque_deposit" class="form-control" readonly placeholder="Date Range">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              <label for="transaction_date_range_cheque_deposit_created">Created On:</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="transaction_date_range_cheque_deposit_created"
                  id="transaction_date_range_cheque_deposit_created" class="form-control" readonly
                  placeholder="Created On">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="cheque_customer_cheque_no">Search by Cheque Number:</label>
              <select name="customer_cheque_no" id="cheque_customer_cheque_no" class="form-control select2"
                style="width: 100%;" placeholder="All">
                <!-- options populated via AJAX -->
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="cheque_customer_amount">Filter by Amount:</label>
              <select name="customer_amount" id="cheque_customer_amount" class="form-control select2"
                style="width: 100%;" placeholder="All">
                <!-- options populated via AJAX -->
              </select>
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="table-responsive">
            <table class="table table-bordered table-striped" id="cheque_list_table" style="width: 100%;">
              <thead>
                <tr style="background-color: #f9f9f9;">
                  <th style="width: 50px; text-align: center;">Select</th>
                  <th>Name</th>
                  <th>Cheque No</th>
                  <th>Cheque Date</th>
                  <th>Bank</th>
                  <th style="text-align: right;">Amount</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label>
                <input type="checkbox" name="encash" value="1" class="input-icheck" id="encash">
                Encash Cheque
              </label>
            </div>
            <div class="form-group">
              <label for="from_account">Deposit To:</label>
              <select name="from_account" id="from_account" class="form-control select2" required style="width: 100%;">
                @foreach($to_accounts as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="note">Note</label>
              <textarea name="note" id="note" class="form-control" placeholder="Note" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label for="attachment">Add Image / Document</label>
              <input type="file" name="attachment" id="attachment" class="form-control" />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary submit_btn">Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
    $('#cheque_customer_cheque_no, #cheque_customer_amount').change(function () {
      get_cheques_list();
    });

    // Populate cheque and amount selects
    $.ajax({
      method: 'get',
      url: '/customer-payment-information/all/cheque_no',
      success: function (result) {
        var select = $('#cheque_customer_cheque_no');
        select.empty();
        select.append($('<option>').val('').text('All'));
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
        select.append($('<option>').val('').text('All'));
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
    });

    $('#transaction_date_range_cheque_deposit_created').daterangepicker(dateRangeSettings, function (start, end) {
      $('#transaction_date_range_cheque_deposit_created').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
      get_cheques_list();
    });

    $('.select2').select2({
      dropdownParent: $('.modal')
    });

    function get_cheques_list() {
      var data = {
        cheque_no: $('#cheque_customer_cheque_no').val(),
        amount: $('#cheque_customer_amount').val(),
        date_range: $('#transaction_date_range_cheque_deposit').val(),
        created_range: $('#transaction_date_range_cheque_deposit_created').val(),
        account_id: $('input[name="account_id"]').val()
      };

      $.ajax({
        method: 'get',
        url: '/deposits-module/get-cheques-list',
        data: data,
        dataType: 'html',
        success: function (result) {
          $('#cheque_list_table tbody').html(result);
        }
      });
    }

    // Initial load
    get_cheques_list();
  });
</script>
</script>
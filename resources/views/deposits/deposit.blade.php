<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    <form action="{{ route('deposits.postDeposit') }}" method="POST" id="deposit_form" enctype="multipart/form-data">
      @csrf

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Deposit ({{ ucfirst($type) }})</h4>
      </div>

      <div class="modal-body">
        <div class="well"
          style="background-color: #f5f5f5; border: 1px solid #e3e3e3; padding: 10px; margin-bottom: 20px;">
          <strong>Selected Account:</strong> {{ $account->name }}<br>
          <strong>Balance:</strong> <span class="text-danger account_balance_text">{{ @num_format($account_balance)
            }}</span>
          <input type="hidden" name="check_insufficient" value="{{ $check_insufficient }}" id="check_insufficient">
          <input type="hidden" name="account_balance" value="{{ round($account_balance, 2) }}" id="account_balance">
        </div>

        @if (!empty($sub_card_accounts))
          <div class="form-group">
            <label for="account_id">Card Accounts:*</label>
            <select class="form-control select2 account_id_deposit" name="account_id" id="account_id" required
              style="width: 100%;">
              <option value="">Please Select</option>
              @foreach ($sub_card_accounts as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
        @else
          <input type="hidden" name="account_id" value="{{ $account->id }}">
        @endif

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="amount_to_submit">Amount:*</label>
              <input class="form-control input_number" required placeholder="Amount" name="amount" type="text"
                id="amount_to_submit">
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="operation_date">Date:*</label>
              <input class="form-control datetimepicker" required placeholder="Date" name="operation_date" type="text"
                value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}" id="operation_date">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="account_group_id">Account Group:*</label>
              <select class="form-control select2" required name="account_group_id" id="account_group_id"
                style="width: 100%;">
                <option value="">Please Select</option>
                @foreach ($account_groups as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="from_account">Deposit To:*</label>
              <select class="form-control select2" required name="from_account" id="from_account" style="width: 100%;">
                <option value="">Please Select</option>
                @foreach ($from_accounts as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        @if ($type != 'cash')
          <div class="form-group">
            <label for="cheque_number">Cheque Number:*</label>
            <input class="form-control" placeholder="Cheque Number" name="cheque_number" type="text" id="cheque_number">
          </div>
        @endif

        <div class="form-group">
          <label for="note">Note</label>
          <textarea class="form-control" placeholder="Note" rows="3" name="note" id="note"></textarea>
        </div>

        <div class="form-group">
          <label for="attachment">Add Image / Document</label>
          <input class="form-control" name="attachment" type="file" id="attachment">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary submit_btn">Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready(function () {
    if ($(".select2").length > 0) {
      $(".select2").select2({
        dropdownParent: $("#generalModalPopup")
      });
    }

    if ($(".datetimepicker").length > 0) {
      $(".datetimepicker").datetimepicker({
        format: "YYYY-MM-DD HH:mm",
        icons: {
          up: "fa fa-angle-up",
          down: "fa fa-angle-down",
          next: "fa fa-angle-right",
          previous: "fa fa-angle-left",
        },
      });
    }

    @if ($group_name != 'Bank Account')
      $('#amount_to_submit').on('change keyup', function () {
        let amount = parseFloat($(this).val());
        let account_balance = parseFloat($('#account_balance').val());

        if (amount > account_balance) {
          toastr.error('@lang('deposits.insufficient_balance_msg')');
          $('.submit_btn').prop('disabled', true);
        } else {
          $('.submit_btn').prop('disabled', false);
        }
      })
    @endif

    @if (!empty($sub_card_accounts))
      $('.account_id_deposit').change(function () {
        let account_id = $(this).val();
        if (account_id) {
          $.ajax({
            method: 'get',
            url: '/deposits-module/get-account-balance/' + account_id,
            dataType: 'json',
            success: function (result) {
              $('.account_balance_text').text(__number_f(result.balance, true));
              $('.selected_account').text(result.name);
              $('#account_balance').val(result.balance);
            },
          });
        }
      })
    @endif

    $('#account_group_id').change(function () {
      let group_id = $(this).val();
      if (group_id) {
        $.ajax({
          method: 'get',
          url: '/deposits-module/get-account-by-group-id/' + group_id,
          data: {},
          contentType: 'html',
          success: function (result) {
            $('#from_account').empty().append(result);
          },
        });
      }
    });
  });
</script>
<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ route('deposits.postDeposit') }}" method="POST" id="deposit_form" enctype="multipart/form-data">
      @csrf

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang('deposits.deposit')</h4>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <strong>@lang('deposits.selected_account')</strong>:
          <span class="selected_account">
            @if(empty($sub_card_accounts))
              {{$account->name}}
            @endif
          </span>
          <span class="text-red pull-right account_balance"> @lang('deposits.balance'):
            @if(!empty($account_balance->balance))
            {{@num_format($account_balance->balance)}} @else {{0.00}} @endif </span>
          <input type="hidden" name="check_insufficient" value="{{ $check_insufficient }}" id="check_insufficient">
          @if(empty($sub_card_accounts))
            <input type="hidden" name="account_balance"
              value="{{ !empty($account_balance->balance) ? round($account_balance->balance, 2) : 0 }}"
              id="account_balance">
          @else
            <input type="hidden" name="account_balance" value="0" id="account_balance">
          @endif
        </div>

        @if(!empty($sub_card_accounts))
          <div class="form-group">
            <label for="account_id">@lang('deposits.card_accounts'):</label>
            <select class="form-control select2 account_id_deposit" name="account_id" id="account_id">
              <option value="">@lang('messages.please_select')</option>
              @foreach($sub_card_accounts as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
        @else
          <input type="hidden" name="account_id" value="{{ $account->id }}">

        @endif


        <div class="form-group">
          <label for="amount_to_submit">@lang('sale.amount'):*</label>
          <input class="form-control input_number" required placeholder="@lang('sale.amount')" name="amount" type="text"
            value="0" id="amount_to_submit">
        </div>

        <div class="form-group">
          <label for="account_group_id">@lang('deposits.account_group'):*</label>
          <select class="form-control select2" required name="account_group_id" id="account_group_id">
            <option value="">@lang('lang_v1.please_select')</option>
            @foreach($account_groups as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="from_account">@lang('deposits.deposit_to'):</label>
          <select class="form-control select2" required name="from_account" id="from_account">
            <option value="">@lang('messages.please_select')</option>
            @foreach($from_accounts as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>


        <div class="form-group chequeDetails">
          <label for="cheque_number">@lang('lang_v1.cheque_number'):*</label>
          <input class="form-control input_number" placeholder="@lang('lang_v1.cheque_number')" name="cheque_number"
            type="text" id="cheque_number">
        </div>

        <div class="form-group">
          <label for="operation_date">@lang('messages.date'):*</label>
          <div class="input-group date" id='od_datetimepicker'>
            <input class="form-control" required placeholder="@lang('messages.date')" name="operation_date" type="text"
              value="0" id="operation_date">
            <span class="input-group-addon">
              <span class="glyphicon glyphicon-calendar"></span>
            </span>
          </div>
        </div>

        <div class="form-group">
          <label for="note">@lang('brand.note')</label>
          <textarea class="form-control" placeholder="@lang('brand.note')" rows="4" name="note" cols="50"
            id="note"></textarea>
        </div>

        <div class="form-group">
          <label for="attachment">@lang('lang_v1.add_image_document')</label>
          <input name="attachment" type="file" id="attachment">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary submit_btn">@lang('messages.submit')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
      </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style>
  .swal-title {
    color: red;
  }
</style>
<script type="text/javascript">
  $(document).ready(function () {
    $(".select2").select2();

    var isCash = "{{!empty($account) ? $account->name : ''}}";
    if (isCash == "Cash") {
      $(".chequeDetails").hide();
    }
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
  });

  @if($group_name != 'Bank Account')
    $('#amount_to_submit').change(function () {//@eng 13/2
      let amount = parseFloat($(this).val());
      account_balance = parseFloat($('#account_balance').val());

      if (amount > account_balance) {
        swal({
          title: '@lang('deposits.insufficient_balance_msg')',
          icon: "error",
          buttons: true,
          dangerMode: true,
        })
        $('.submit_btn').prop('disabled', true);
      } else {
        $('.submit_btn').prop('disabled', false);
      }
    })
  @endif

  @if(!empty($sub_card_accounts))
    $('.account_id_deposit').change(function () {
      account_id = $(this).val();
      $.ajax({
        method: 'get',
        url: '/deposits-module/get-account-balance/' + account_id,
        data: {},
        success: function (result) {
          $('.account_balance').text('Balance:' + __number_f(result.balance, false));
          $('.selected_account').text(result.name);
          $('#account_balance').val(result.balance);
        },
      });
    })
  @endif
  $('#account_group_id').change(function () {
    $.ajax({
      method: 'get',
      url: '/deposits-module/get-account-by-group-id/' + $(this).val(),
      data: {},
      contentType: 'html',
      success: function (result) {
        $('#from_account').empty().append(result);
      },
    });
  })
</script>
<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ route('deposits.postFundTransfer') }}" method="POST" id="fund_transfer_form"
      enctype="multipart/form-data">
      @csrf

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang('deposits.fund_transfer')</h4>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label for="from_account">@lang('deposits.transfer_from'):*</label>
          <select class="form-control select2" required name="from_account" id="from_account"
            placeholder="@lang('lang_v1.please_select')">
            <option value="" selected disabled>@lang('lang_v1.please_select')</option>
            @foreach($to_accounts as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="to_account">@lang('deposits.transfer_to'):*</label>
          <select class="form-control select2" required name="to_account" id="to_account"
            placeholder="@lang('lang_v1.please_select')">
            <option value="" selected disabled>@lang('lang_v1.please_select')</option>
            @foreach($to_accounts as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="amount">@lang('sale.amount'):*</label>
          <input class="form-control input_number" required placeholder="@lang('sale.amount')" name="amount" type="text"
            value="0" id="amount">
        </div>


        <div class="form-group">
          <label for="cheque_number">@lang('lang_v1.cheque_number'):*</label>
          <input class="form-control input_number" required placeholder="@lang('lang_v1.cheque_number')"
            name="cheque_number" type="text" id="cheque_number">
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
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });

    $(".select2").select2();
  });


  $('#account_group_id').change(function () {
    $.ajax({
      method: 'get',
      url: '/deposits-module/get-account-by-group-id/' + $(this).val(),
      data: {},
      contentType: 'html',
      success: function (result) {
        $('#to_account').empty().append(result);
      },
    });
  })
</script>
<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'postRefundPayment'], $contact_id) }}" method="post" id="pay_contact_due_form" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="contact_id" value="{{ $contact_details->id }}">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.refund_cheque_return' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-6">
          <div class="well">
            @if($contact_details->type == 'customer')
            <strong>@lang('lang_v1.customer'):
              @else
              <strong>@lang('lang_v1.supplier'):
                @endif
              </strong>{{ $contact_details->name }}<br>
          </div>
        </div>
        <input type="hidden" name="type_hidden" value="advance_payment">
      </div>
      <div class="row payment_row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="type">{{ __('lang_v1.type') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              <select name="type" class="form-control" required id="type">
                  <option value="">{{ __('lang_v1.please_select') }}</option>
                  <option value="refund">{{ __('lang_v1.refund') }}</option>
                  <option value="cheque_return">{{ __('lang_v1.cheque_return') }}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="amount">{{ __('sale.amount') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              <input type="text" name="amount" class="form-control input_number" data-rule-min-value="0" data-msg-min-value="{{ __('lang_v1.negative_value_not_allowed') }}" required placeholder="Amount" id="amount">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="paid_on_date">{{ __('lang_v1.paid_on') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              <input type="text" name="paid_on" value="{{ date('m/d/Y') }}" class="form-control paid_on_date" placeholder="{{ __('lang_v1.paid_on') }}" id="paid_on_date">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="method">{{ __('purchase.payment_method') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              <select name="method" class="form-control select2 payment_types_dropdown_refund" required style="width:100%;" id="method">
                  @foreach($payment_types as $key => $val)
                      <option value="{{ $key }}">{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-4 cheque_return_charges_div hide">
          <div class="form-group">
            <label for="cheque_bank">{{ __('lang_v1.bank') }}</label>
            <select name="cheque_bank" class="form-control select2 cheque_bank" id="cheque_bank" style="width:100%;">
                <option value="">{{ __('lang_v1.please_select') }}</option>
                @foreach($cheque_banks as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4 cheque_return_charges_div hide">
          <div class="form-group">
            <label for="cheque_number_return">{{ __('lang_v1.cheque_number') }}</label>
            <select name="cheque_number_return" class="form-control select2 cheque_number_return" id="cheque_number_return" style="width:100%;">
                <option value="">{{ __('lang_v1.please_select') }}</option>
                @foreach($cheque_array as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-6 sale_invoice_bill_number_div hide">
          <div class="form-group">
            <label for="sale_invoice_bill_number">{{ __('lang_v1.sale_invoice_bill_number') }}</label>
            <select name="sale_invoice_bill_number" class="form-control select2 sale_invoice_bill_number" id="sale_invoice_bill_number" style="width:100%;">
                <option value="">{{ __('lang_v1.please_select') }}</option>
                @foreach($invoices as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
            </select>
          </div>
        </div>
        
        <div class="clearfix"></div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="document">{{ __('purchase.attach_document') }}:</label>
            <input type="file" name="document">
          </div>
        </div>

        <div class="col-md-6 account_id_div hide">
          <div class="form-group">
            <label for="account_id">{{ __('lang_v1.payment_account') }}:</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              <select name="account_id" class="form-control select2" id="account_id" style="width:100%;">
                  <option value="">{{ __('lang_v1.please_select') }}</option>
                  @foreach($accounts as $key => $val)
                      <option value="{{ $key }}" @if($customer_deposit_account_id == $key) selected @endif>{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="clearfix"></div>

        @include('pages.contacts.partials.refund_payment_type_details')
        
        <div class="col-md-12">
          <div class="form-group">
            <label for="note">{{ __('lang_v1.payment_note') }}:</label>
            <textarea name="note" class="form-control" rows="3"></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary" id="submit_btn">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $('#pay_contact_due_form').validate();

    $('#type').change(function(){
        if($(this).val() === 'refund'){
            $('.cheque_return_charges_div').addClass('hide');
            $('.sale_invoice_bill_number_div').removeClass('hide');
            $('#amount').attr('readonly', false);
            $('.payment_types_dropdown_refund').attr('disabled', false);
        }else if($(this).val() === 'cheque_return'){
            $('.cheque_return_charges_div').removeClass('hide');
            $('.sale_invoice_bill_number_div').addClass('hide');
            $('#amount').attr('readonly', true);
            $('.payment_types_dropdown_refund').attr('disabled', true);
            $('.payment_types_dropdown_refund').val('bank_transfer').trigger('change');
        }else{
            $('.cheque_return_charges_div').addClass('hide');
            $('.sale_invoice_bill_number_div').addClass('hide');
            $('#amount').attr('readonly', false);
            $('.payment_types_dropdown_refund').attr('disabled', false);
        }
    })
    
    $(document).on('click','#amount',function(){
       $("#amount").val("");
    });

    $(document).on('change', '#cheque_number_return', function () {
      let payment_id = $(this).val();
        $.ajax({
          method: 'get',
          url: '/contacts/get-payment-details-by-id/'+payment_id,
          success: function(result) {
            $('#amount').val(result.amount);
        },
      });
    });

    $(document).on('change', '#cheque_bank', function () {
      let bank_id = $(this).val();
      $.ajax({
        method: 'get',
        url: '/contacts/get-cheque-dropdown/'+bank_id+'/{{ $contact_details->id }}',
        contentType: 'html',
        success: function(result) {
          $('#cheque_number_return').empty().append(result);
        },
      });
    });
</script>

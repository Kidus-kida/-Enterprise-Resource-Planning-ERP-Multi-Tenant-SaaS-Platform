<div class="modal-dialog" role="document">
  <div class="modal-content">

    <form action="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'postPayContactDue']) }}" method="post" id="pay_contact_due_form" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="contact_id" value="{{ $contact_details->contact_id }}">
    <input type="hidden" name="due_payment_type" value="{{ $due_payment_type }}">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
          aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'purchase.add_payment' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        @if($due_payment_type == 'purchase')
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('purchase.supplier'): </strong>{{ $contact_details->name }}<br>
            <strong>@lang('business.business'): </strong>{{ $contact_details->supplier_business_name }}<br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('report.total_purchase'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_purchase }}</span><br>
            <strong>@lang('contact.total_paid'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_paid }}</span><br>
            <strong>@lang('contact.total_purchase_due'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_purchase - $contact_details->total_paid }}</span><br>
            @if(!empty($contact_details->opening_balance) || $contact_details->opening_balance != '0.00')
            <strong>@lang('lang_v1.opening_balance'): </strong>
            <span class="display_currency" data-currency_symbol="true">
              {{ $contact_details->opening_balance }}</span><br>
            <strong>@lang('lang_v1.opening_balance_due'): </strong>
            <span class="display_currency" data-currency_symbol="true">
              {{ $ob_due }}</span>
            @endif
          </div>
        </div>
        @elseif($due_payment_type == 'purchase_return')
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('purchase.supplier'): </strong>{{ $contact_details->name }}<br>
            <strong>@lang('business.business'): </strong>{{ $contact_details->supplier_business_name }}<br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('lang_v1.total_purchase_return'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_purchase_return }}</span><br>
            <strong>@lang('lang_v1.total_purchase_return_paid'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_return_paid }}</span><br>
            <strong>@lang('lang_v1.total_purchase_return_due'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_purchase_return - $contact_details->total_return_paid }}</span>
          </div>
        </div>
        @elseif(in_array($due_payment_type, ['sell']))
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('sale.customer_name'): </strong>{{ $contact_details->name }}<br>
            <br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('report.total_sell'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_invoice }}</span><br>
            <strong>@lang('contact.total_paid'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_paid }}</span><br>
            <strong>@lang('contact.total_sale_due'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_invoice - $contact_details->total_paid }}</span><br>
            @if(!empty($contact_details->opening_balance) || $contact_details->opening_balance != '0.00')
            <strong>@lang('lang_v1.opening_balance'): </strong>
            <span class="display_currency" data-currency_symbol="true">
              {{ $contact_details->opening_balance }}</span><br>
            <strong>@lang('lang_v1.opening_balance_due'): </strong>
            <span class="display_currency" data-currency_symbol="true">
              {{ $ob_due }}</span>
            @endif
          </div>
        </div>
        @elseif(in_array($due_payment_type, ['sell_return']))
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('sale.customer_name'): </strong>{{ $contact_details->name }}<br>
            <br><br>
          </div>
        </div>
        <div class="col-md-6">
          <div class="well">
            <strong>@lang('lang_v1.total_sell_return'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_sell_return }}</span><br>
            <strong>@lang('lang_v1.total_sell_return_paid'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_return_paid }}</span><br>
            <strong>@lang('lang_v1.total_sell_return_due'): </strong><span class="display_currency"
              data-currency_symbol="true">{{ $contact_details->total_sell_return - $contact_details->total_return_paid }}</span>
          </div>
        </div>
        @endif
      </div>
      <div class="row payment_row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="location_id">{{ __('purchase.business_location') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-location-arrow"></i>
              </span>
              <select name="location_id" class="form-control select2 location_id" required style="width:100%;">
                  <option value="">{{ __('lang_v1.please_select') }}</option>
                  @foreach($business_locations as $key => $val)
                      <option value="{{ $key }}" @if($business_location_id == $key) selected @endif>{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="payment_ref_no">{{ __('lang_v1.ref_no') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-link"></i>
              </span>
              <input type="text" name="payment_ref_no" value="{{ $payment_ref_no }}" class="form-control payment_ref_no" readonly style="width:100%;" placeholder="{{ __('lang_v1.ref_no') }}">
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
              <input type="text" name="amount" value="{{ $amount_formated }}" class="form-control input_number" data-rule-min-value="0" data-rule-max-value="{{ $amount_formated }}" data-msg-max-value="{{ __('contact.greater_value_not_allowed') }}" data-msg-min-value="{{ __('lang_v1.negative_value_not_allowed') }}" required placeholder="Amount">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="paid_on">{{ __('lang_v1.paid_on') }}:*</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              <input type="text" name="paid_on" value="{{ \Carbon\Carbon::parse($payment_line->paid_on)->format('m/d/Y H:i') }}" class="form-control" readonly required>
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
              <select name="method" class="form-control select2 payment_types_dropdown" required style="width:100%;">
                  @foreach($payment_types as $key => $val)
                      <option value="{{ $key }}" @if($payment_line->method == $key) selected @endif>{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="document">{{ __('purchase.attach_document') }}:</label>
            <input type="file" name="document">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="account_id">{{ __('lang_v1.payment_account') }}:</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>

              <select name="account_id" class="form-control select2 account_id" id="account_id" style="width:100%;">
                  <option value="">{{ __('lang_v1.please_select') }}</option>
                  @foreach($accounts as $key => $val)
                      <option value="{{ $key }}" @if((!empty($payment_line->account_id) ? $payment_line->account_id : '') == $key) selected @endif>{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="clearfix"></div>

        @include('pages.contacts.partials.payment_type_details')
        
        <div class="col-md-6 text-left" >
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="post_dated_cheque" value="1" class="input-icheck" id="post_dated_cheque"> {{ __( 'account.post_dated_cheque' ) }}
                </label>
            </div>
        </div>
        
        <div class="col-md-6 text-left" >
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="update_post_dated_cheque" value="1" class="input-icheck" id="update_post_dated_cheque"> {{ __( 'account.update_post_dated_cheque' ) }}
                </label>
            </div>
        </div>
        
        <div class="col-md-12">
          <div class="form-group">
            <label for="note">{{ __('lang_v1.payment_note') }}:</label>
            <textarea name="note" class="form-control" rows="3">{{ $payment_line->note }}</textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary submit_btn" id="submit_btn">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    </form>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
  $('.payment_types_dropdown').trigger('change');
  $('#pay_contact_due_form').validate();
  $(".select2").select2();

  $(document).on('change', '.location_id', function(){
    let location_id = $(this).val();
    $.ajax({
      method: 'get',
      url: "/contacts/get-payment-method-by-location-id/"+location_id,
      data: {  },
      contentType: 'html',
      success: function(result) {
        if(result){
          $('#method').empty().append(result);
          $('#method option:eq(0)').prop('selected', 'selected');
          $('.payment_types_dropdown').trigger('change');
        }
      },
    });
  })
</script>

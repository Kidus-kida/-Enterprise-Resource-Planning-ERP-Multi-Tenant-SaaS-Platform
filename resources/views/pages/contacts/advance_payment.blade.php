<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      <form action="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'postAdvancePayment'], $contact_id) }}" method="post" id="pay_contact_due_form" enctype="multipart/form-data">
      @csrf

      <input type="hidden" name="contact_id" value="{{ $contact_details->id }}">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'lang_v1.advance_payment' )</h4>
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
          <input type="hidden" name="type" value="advance_payment">
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
                <input type="text" name="amount" class="form-control input_number" data-rule-min-value="0" data-msg-min-value="{{ __('lang_v1.negative_value_not_allowed') }}" required placeholder="Amount">
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
                <input type="text" name="paid_on" value="{{ date('m/d/Y') }}" class="form-control" readonly required>
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
                        <option value="{{ $key }}">{{ $val }}</option>
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
            <div class="col-md-6 account_id_div">
              <div class="form-group">
                <label for="account_id">{{ __('lang_v1.payment_account') }}:</label>
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-money"></i>
                  </span>
                  <select name="account_id" class="form-control select2 account_id" id="account_id" style="width:100%;">
                      <option value="">{{ __('lang_v1.please_select') }}</option>
                      @foreach($accounts as $key => $val)
                          <option value="{{ $key }}" @if($customer_deposit_account_id == $key) selected @endif>{{ $val }}</option>
                      @endforeach
                  </select>
                </div>
              </div>
            </div>
          <div class="clearfix"></div>
  
            @include('pages.contacts.partials.advance_payment_type_details')
            
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
      $('.payment_types_dropdown').trigger('change');
      $('#pay_contact_due_form').validate();
      
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

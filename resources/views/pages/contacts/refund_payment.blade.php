<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\Contacts\Http\Controllers\ContactController::class, 'postRefundPayment'], $contact_id), 'method' => 'post',
    'id' => 'pay_contact_due_form', 'files' => true ]) !!}

    {!! Form::hidden("contact_id", $contact_details->id); !!}
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
            {!! Form::label("type" , __('lang_v1.type') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              {!! Form::select("type", ['refund' => __('lang_v1.refund'), 'cheque_return' =>
              __('lang_v1.cheque_return')], null , ['class' => 'form-control', 'required', 'placeholder' =>
              __('lang_v1.please_select'), 'id' => 'type']); !!}
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("amount" , __('sale.amount') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              {!! Form::text("amount", null, ['class' => 'form-control input_number', 'data-rule-min-value' => 0,
              'data-msg-min-value' => __('lang_v1.negative_value_not_allowed'), 'required', 'placeholder' => 'Amount']);
              !!}
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("paid_on" , __('lang_v1.paid_on') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              {!! Form::text( "paid_on", date('m/d/Y'), ['class' => 'form-control paid_on_date', 'placeholder' => __('lang_v1.paid_on') , 'id' => 'paid_on_date']); !!}
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label("method" , __('purchase.payment_method') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              {!! Form::select("method", $payment_types, null, ['class' => 'form-control select2
              payment_types_dropdown_refund', 'required', 'style' => 'width:100%;', 'id' => 'method']); !!}
            </div>
          </div>
        </div>
        <div class="col-md-4 cheque_return_charges_div hide">
          <div class="form-group">
            {!! Form::label("cheque_bank",__('lang_v1.bank')) !!}
            {!! Form::select("cheque_bank", $cheque_banks, null, ['class' => 'form-control select2 cheque_bank', 'placeholder' => __('lang_v1.please_select')]); !!}
          </div>
        </div>
        <div class="col-md-4 cheque_return_charges_div hide">
          <div class="form-group">
            {!! Form::label("cheque_number_return",__('lang_v1.cheque_number')) !!}
            {!! Form::select("cheque_number_return", $cheque_array, null, ['class' => 'form-control select2 cheque_number_return', 'placeholder' => __('lang_v1.please_select')]); !!}
          </div>
        </div>
        <div class="col-md-6 sale_invoice_bill_number_div hide">
          <div class="form-group">
            {!! Form::label("sale_invoice_bill_number",__('lang_v1.sale_invoice_bill_number')) !!}
            {!! Form::select("sale_invoice_bill_number", $invoices, null, ['class' => 'form-control select2 sale_invoice_bill_number', 'placeholder' => __('lang_v1.please_select')]); !!}
          </div>
        </div>
        
        <div class="clearfix"></div>
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('document', __('purchase.attach_document') . ':') !!}
            {!! Form::file('document'); !!}
          </div>
        </div>

        <div class="col-md-6 account_id_div hide">
          <div class="form-group">
            {!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              {!! Form::select("account_id", $accounts, $customer_deposit_account_id , ['class' => 'form-control select2', 'placeholder' => __('lang_v1.please_select'), 'id' => "account_id", 'style' => 'width:100%;']); !!}
            </div>
          </div>
        </div>

        <div class="clearfix"></div>

        @include('pages.contacts.partials.refund_payment_type_details')
        
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label("note", __('lang_v1.payment_note') . ':') !!}
            {!! Form::textarea("note", null, ['class' => 'form-control', 'rows' => 3]); !!}
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary" id="submit_btn">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

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

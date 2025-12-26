<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      <form action="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'postDirectLoan'], $contact_id) }}" method="post" id="pay_contact_due_form" enctype="multipart/form-data">
      @csrf

      <input type="hidden" name="contact_id" value="{{ $contact_details->id }}">
      <div class="modal-header">
        <h4 class="modal-title">Direct Loan</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
  
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="well">
              @if($contact_details->type == 'customer')
              <strong>Customer: 
                @else
                <strong>Supplier: 
              @endif
              </strong>{{ $contact_details->name }}<br>
            </div>
          </div>
          <input type="hidden" name="type" value="advance_payment">
        </div>
        <div class="row payment_row"> 
          
          <div class="col-md-6">
            <div class="form-group">
              <label for="amount">Amount:*</label>
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fa-money"></i>
                </span>
                <input type="text" name="amount" class="form-control input_number" data-rule-min-value="0" data-msg-min-value="Negative value not allowed" required placeholder="Amount" id="amount">
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-group">
              <label for="user">Created By:*</label>
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fa-user"></i>
                </span>
                <input type="text" name="user" value="{{ auth()->user()->username }}" class="form-control" disabled>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-group">
              <label for="method">Payment Method:*</label>
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fa-money"></i>
                </span>
                <select name="method" class="form-control select2" disabled style="width:100%;">
                    @foreach($accounts as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
              </div>
            </div>
          </div>
          
        <div class="col-md-6 account_id_div">
          <div class="form-group">
            <label for="account_id">Payment Account:</label>
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-money"></i>
              </span>
              <select name="account_id" class="form-control select2" id="account_id" style="width:100%;" disabled>
                  @foreach($accounts as $key => $val)
                      <option value="{{ $key }}">{{ $val }}</option>
                  @endforeach
              </select>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
              <label for="paid_on">Paid On:*</label>
              <div class="input-group">
                <span class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </span>
                <input type="text" name="paid_on" value="{{ date('m/d/Y') }}" class="form-control" readonly required>
              </div>
            </div>
          </div>
        
        
          <div class="col-md-12">
            <div class="form-group">
              <label for="note">Payment Note:</label>
              <textarea name="note" class="form-control" rows="3"></textarea>
            </div>
          </div>
          
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
  
      </form>
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->

  <script>
      $('#pay_contact_due_form').validate();
       $(document).on('click','#amount',function(){
       $("#amount").val("");
    });
  </script>

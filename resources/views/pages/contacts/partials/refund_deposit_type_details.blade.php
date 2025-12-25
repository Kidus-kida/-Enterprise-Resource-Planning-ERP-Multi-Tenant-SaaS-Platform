<div class="hide bank_transfer_fields">
    @if($contact_details->type == 'supplier')
      <div class="col-md-6">
            <div class="form-group">
                <label for="bank_name">{{ __('lang_v1.bank_name') }}</label>
                <select name="bank_name" class="form-control" id="bank_name">
                    <option value="">{{ __('lang_v1.please_select') }}</option>
                    @foreach($bank_accounts->pluck('name', 'name') as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
	<div class="col-md-6">
		<div class="form-group">
			<label for="cheque_number">{{ __('lang_v1.cheque_no') }}</label>
			<input type="text" name="cheque_number" class="form-control" placeholder="{{ __('lang_v1.cheque_no') }}">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="cheque_date">{{ __('lang_v1.cheque_date') }}</label>
			<input type="text" name="cheque_date" class="form-control cheque_date" placeholder="{{ __('lang_v1.cheque_date') }}">
		</div>
	</div>
</div>

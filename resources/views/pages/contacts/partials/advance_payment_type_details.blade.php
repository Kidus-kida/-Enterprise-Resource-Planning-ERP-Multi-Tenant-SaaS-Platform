<div class="payment_details_div hide" data-type="card" >
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_number">{{ __('lang_v1.card_no') }}</label>
			<input type="text" name="card_number" class="form-control" placeholder="{{ __('lang_v1.card_no') }}">
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_holder_name">{{ __('lang_v1.card_holder_name') }}</label>
			<input type="text" name="card_holder_name" class="form-control" placeholder="{{ __('lang_v1.card_holder_name') }}">
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_transaction_number">{{ __('lang_v1.card_transaction_no') }}</label>
			<input type="text" name="card_transaction_number" class="form-control" placeholder="{{ __('lang_v1.card_transaction_no') }}">
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_type">{{ __('lang_v1.card_type') }}</label>
			<select name="card_type" class="form-control select2">
                @foreach(['credit' => 'Credit Card', 'debit' => 'Debit Card', 'visa' => 'Visa', 'master' => 'MasterCard'] as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
            </select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_month">{{ __('lang_v1.month') }}</label>
			<input type="text" name="card_month" class="form-control" placeholder="{{ __('lang_v1.month') }}">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_year">{{ __('lang_v1.year') }}</label>
			<input type="text" name="card_year" class="form-control" placeholder="{{ __('lang_v1.year') }}">
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_security">{{ __('lang_v1.security_code') }}</label>
			<input type="text" name="card_security" class="form-control" placeholder="{{ __('lang_v1.security_code') }}">
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div class="payment_details_div hide" data-type="cheque" >
	
	<div class="col-md-6">
		<div class="form-group">
			<label for="bank_name">Bank Name</label>
			<input type="text" name="bank_name" class="form-control" placeholder="Bank Name">
		</div>
	</div>
	
	
</div>
<div class="bank_transfer_fields hide" data-type="bank_transfer" >
	<div class="col-md-6">
		<div class="form-group">
			<label for="cheque_number">{{ __('lang_v1.cheque_no') }}</label>
			<input type="text" name="cheque_number" class="form-control" placeholder="{{ __('lang_v1.cheque_no') }}">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="cheque_date">{{ __('lang_v1.cheque_date') }}</label>
			<input type="date" name="cheque_date" class="form-control cheque_date" placeholder="{{ __('lang_v1.cheque_date') }}">
		</div>
	</div>
</div>
<div class="payment_details_div hide" data-type="custom_pay_1" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_1">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_1" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}">
		</div>
	</div>
</div>
<div class="payment_details_div hide" data-type="custom_pay_2" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_2">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_2" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}">
		</div>
	</div>
</div>
<div class="payment_details_div hide" data-type="custom_pay_3" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_3">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_3" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}">
		</div>
	</div>
</div>

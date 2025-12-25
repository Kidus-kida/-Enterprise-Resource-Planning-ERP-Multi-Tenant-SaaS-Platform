<div class="payment_details_div @if( $payment_line->method !== 'card' ) {{ 'hide' }} @endif" data-type="card" >
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_number">{{ __('lang_v1.card_no') }}</label>
			<input type="text" name="card_number" value="{{ $payment_line->card_number }}" class="form-control" placeholder="{{ __('lang_v1.card_no') }}">
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_holder_name">{{ __('lang_v1.card_holder_name') }}</label>
			<input type="text" name="card_holder_name" value="{{ $payment_line->card_holder_name }}" class="form-control" placeholder="{{ __('lang_v1.card_holder_name') }}">
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="card_transaction_number">{{ __('lang_v1.card_transaction_no') }}</label>
			<input type="text" name="card_transaction_number" value="{{ $payment_line->card_transaction_number }}" class="form-control" placeholder="{{ __('lang_v1.card_transaction_no') }}">
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_type">{{ __('lang_v1.card_type') }}</label>
			<select name="card_type" class="form-control select2">
                @foreach(['credit' => 'Credit Card', 'debit' => 'Debit Card', 'visa' => 'Visa', 'master' => 'MasterCard'] as $key => $val)
                    <option value="{{ $key }}" @if($payment_line->card_type == $key) selected @endif>{{ $val }}</option>
                @endforeach
            </select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_month">{{ __('lang_v1.month') }}</label>
			<select class="form-control" id="card_month" name="card_month">
			@php for ($i=1; $i<=12; $i+=1) { @endphp
				<option value="{{$i}}" @if($payment_line->card_month == $i){{ "selected"}} @endif >{{$i}}</option>
			@php } @endphp
			</select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_year">{{ __('lang_v1.year') }}</label>
			<select class="form-control" id="card_year" name="card_year">
			@php for ($i=date('y'); $i<=date('y')+20; $i+=1) { @endphp
				<option value="{{$i}}" @if($payment_line->card_year == $i){{ "selected"}} @endif >{{$i}}</option>
			@php } @endphp
			</select>
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			<label for="card_security">{{ __('lang_v1.security_code') }}</label>
			<input type="text" name="card_security" value="{{ $payment_line->card_security }}" class="form-control" placeholder="{{ __('lang_v1.security_code') }}">
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'cheque' && $payment_line->method !== 'bank_transfer' ) {{ 'hide' }} @endif add_payment_bank_details" data-type="cheque" >
	<div class="col-md-6">
		<div class="form-group">
			<label for="cheque_number">{{ __('lang_v1.cheque_no') }}</label>
			<input type="text" name="cheque_number" value="{{ $payment_line->cheque_number }}" class="form-control" placeholder="{{ __('lang_v1.cheque_no') }}">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group bankName">
			<label for="bank_name">Bank Name</label>
			<input type="text" name="bank_name" value="{{ $payment_line->bank_name }}" class="form-control" placeholder="Bank Name">
		</div>
	</div>
	<div class="col-md-12">
		<div class="form-group">
			<label for="cheque_date">{{ __('lang_v1.cheque_date') }}</label>
			<input type="text" name="cheque_date" value="{{ $payment_line->cheque_date }}" class="form-control cheque_date" placeholder="{{ __('lang_v1.cheque_date') }}">
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_1' ) {{ 'hide' }} @endif" data-type="custom_pay_1" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_1">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_1" value="{{ $payment_line->transaction_no }}" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}" disabled>
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_2' ) {{ 'hide' }} @endif" data-type="custom_pay_2" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_2">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_2" value="{{ $payment_line->transaction_no }}" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}" disabled>
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_3' ) {{ 'hide' }} @endif" data-type="custom_pay_3" >
	<div class="col-md-12">
		<div class="form-group">
			<label for="transaction_no_3">{{ __('lang_v1.transaction_no') }}</label>
			<input type="text" name="transaction_no_3" value="{{ $payment_line->transaction_no }}" class="form-control" placeholder="{{ __('lang_v1.transaction_no') }}" disabled>
		</div>
	</div>
</div>

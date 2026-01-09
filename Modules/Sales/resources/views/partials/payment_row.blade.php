@php
    $row_index = $row_index ?? 0;
@endphp
<div class="payment-row row mb-3 p-3 border rounded bg-light payment_row" data-row-index="{{ $row_index }}">
    <div class="col-12 d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">{{ __('Payment') }} #{{ $row_index + 1 }}</h6>
        @if($row_index > 0)
        <button type="button" class="btn btn-danger btn-sm remove_payment_row">
            <i class="fas fa-times"></i>
        </button>
        @endif
    </div>

    <div class="col-md-3">
        <div class="input-block mb-3">
            <x-form.label>{{ __('Amount') }}</x-form.label>
            <input type="number" name="payment[{{ $row_index }}][amount]" class="form-control payment_amount" value="0" step="0.01">
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-block mb-3">
            <x-form.label>{{ __('Payment Method') }}</x-form.label>
            <select name="payment[{{ $row_index }}][method]" class="form-select payment_method">
                <option value="" selected disabled>-- {{ __('select method') }} --</option>
                @foreach($payment_types as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-block mb-3">
            <x-form.label>{{ __('Payment Account') }}</x-form.label>
            <select name="payment[{{ $row_index }}][account_id]" class="form-select payment_account">
                <option value="">{{ __('Select Account') }}</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-block mb-3">
            <x-form.label>{{ __('Payment Note') }}</x-form.label>
            <input type="text" name="payment[{{ $row_index }}][note]" class="form-control" placeholder="{{ __('Note') }}">
        </div>
    </div>
    
    {{-- Method-specific fields (Hidden by default or dynamic) --}}
    <div class="col-md-12 method_fields hide" id="method_fields_{{ $row_index }}">
        <div class="row">
            <div class="col-md-4 cheque_fields hide">
                <div class="input-block mb-3">
                    <label>{{ __('Cheque No') }}</label>
                    <input type="text" name="payment[{{ $row_index }}][cheque_number]" class="form-control">
                </div>
            </div>
            <div class="col-md-4 card_fields hide">
                <div class="input-block mb-3">
                    <label>{{ __('Transaction No') }}</label>
                    <input type="text" name="payment[{{ $row_index }}][transaction_no]" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment Row Partial --}}
@php
    $row_index = $row_index ?? 0;
    $payment = $payment ?? null;
    $payment_method = $payment['method'] ?? $payment->method ?? '';
@endphp

<div class="payment-row row mb-3 p-3 border rounded bg-light" data-row-index="{{ $row_index }}">
    <input type="hidden" class="payment_row_index" value="{{ $row_index }}">
    
    <div class="col-12 d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Payment #{{ $row_index + 1 }}</h6>
        <button type="button" class="btn btn-danger btn-sm remove-payment-row">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="col-md-4">
        <x-form.label>{{ __('Payment Amount') }} *</x-form.label>
        <x-form.input 
            type="number" 
            step="0.01" 
            class="form-control payment-amount" 
            name="payment[{{ $row_index }}][amount]" 
            value="{{ $payment->amount ?? '0.00' }}"
            id="amount_{{ $row_index }}"
            required />
    </div>
    
    <div class="col-md-4">
        <x-form.label>{{ __('Payment Method') }} *</x-form.label>
        <x-form.select 
            class="form-control payment_types_dropdown payment-method" 
            name="payment[{{ $row_index }}][method]"
            id="method_{{ $row_index }}"
            >
            <option value="">{{ __('Select Method') }}</option>
            @if(isset($payment_types))
                @foreach($payment_types as $key => $value)
                    <option value="{{ $key }}" {{ $payment_method == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            @else
                <option value="cash" {{ $payment_method == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                <option value="bank_transfer" {{ $payment_method == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                <option value="cheque" {{ $payment_method == 'cheque' ? 'selected' : '' }}>{{ __('Cheque') }}</option>
                <option value="credit_purchase" {{ $payment_method == 'credit_purchase' ? 'selected' : '' }}>{{ __('Credit Purchase') }}</option>
            @endif
        </x-form.select>
    </div>
    
    <div class="col-md-4 account_module" style="display:none;">
        <x-form.label>{{ __('Payment Account') }}</x-form.label>
        <x-form.select 
            class="form-control account_id" 
            name="payment[{{ $row_index }}][account_id]"
            id="account_{{ $row_index }}">
            <option value="">{{ __('Select Account') }}</option>
            @if(isset($accounts))
                @foreach($accounts as $acc_id => $acc_name)
                    <option value="{{ $acc_id }}" {{ ($payment->account_id ?? '') == $acc_id ? 'selected' : '' }}>
                        {{ $acc_name }}
                    </option>
                @endforeach
            @endif
        </x-form.select>
    </div>
    
    {{-- Cheque/Bank Transfer Fields --}}
    <div class="payment_details_div @if( $payment_method !== 'card' ) {{ 'd-none' }} @endif" data-type="card" >
        <div class="col-md-4">
            <div class="form-group">
                <x-form.label for="card_transaction_number_{{ $row_index }}">{{ __('Card Transaction No') }}</x-form.label>
                <x-form.input 
                    type="text"
                    class="form-control"
                    name="payment[{{ $row_index }}][card_transaction_number]"
                    value="{{ $payment->card_transaction_number ?? '' }}"
                    placeholder="{{ __('Card Transaction No') }}"
                    id="card_transaction_number_{{ $row_index }}"
                />
            </div>
        </div>
    </div>

    <!-- Cheque List Section -->
    <div class="payment_details_div cheque_payment_details_only @if($payment_method !== 'cheque') d-none @endif">
        <div class="col-md-6">
            <div class="form-group">
                <x-form.label for="transaction_date_range_cheque_deposit_{{ $row_index }}">{{ __('Date Range') }}:</x-form.label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input 
                        type="text" 
                        name="transaction_date_range_cheque_deposit"
                        value="" 
                        class="form-control cheque_date_range" 
                        readonly 
                        placeholder="{{ __('Date Range') }}" 
                        id="transaction_date_range_cheque_deposit_{{ $row_index }}" 
                        data-row_index="{{ $row_index }}"
                    >
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="cheque_list_table_{{$row_index}}">
                    <thead>
                        <tr>
                            <th>{{__('Select')}}</th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Cheque No')}}</th>
                            <th>{{__('Cheque Date')}}</th>
                            <th>{{__('Bank')}}</th>
                            <th>{{__('Amount')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-12 bank_transfer_fields mt-2" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <x-form.label>{{ __('Cheque Number') }}</x-form.label>
                <x-form.input 
                    type="text" 
                    class="form-control" 
                    name="payment[{{ $row_index }}][cheque_number]"
                    value="{{ $payment->cheque_number ?? '' }}" />
            </div>
            <div class="col-md-6">
                <x-form.label>{{ __('Cheque Date') }}</x-form.label>
                <x-form.input 
                    type="date" 
                    class="form-control cheque_date" 
                    name="payment[{{ $row_index }}][cheque_date]"
                    value="{{ $payment->cheque_date ?? '' }}" />
            </div>
        </div>
    </div>
    
    {{-- Payment Note --}}
    <div class="col-md-12 mt-2">
        <x-form.label>{{ __('Payment Note') }}</x-form.label>
        <x-form.textarea 
            name="payment[{{ $row_index }}][note]" 
            rows="2"
            class="form-control">{{ $payment->note ?? '' }}</x-form.textarea>
    </div>
</div>

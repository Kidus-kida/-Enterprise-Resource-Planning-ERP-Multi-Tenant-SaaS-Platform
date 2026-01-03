<div class="payment-row mb-3 p-3 border rounded bg-white position-relative">
    @if(!empty($removable))
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove_payment_row" aria-label="Close"></button>
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('Amount') }}</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="payment[{{ $row_index }}][amount]" class="form-control payment_amount" step="0.01" value="{{ number_format($payment_line['amount'] ?? 0, 2, '.', '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Payment Method') }}</label>
            <select name="payment[{{ $row_index }}][method]" class="form-select payment_method">
                @foreach($payment_types as $val => $label)
                    <option value="{{ $val }}" @if(!empty($payment_line['method']) && $payment_line['method'] == $val) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12 account-field">
            <label class="form-label">{{ __('Payment Account') }}</label>
            <select name="payment[{{ $row_index }}][account_id]" class="form-select select2">
                <option value="">{{ __('Select Account') }}</option>
                @foreach($accounts as $id => $name)
                    <option value="{{ $id }}" @if(!empty($payment_line['account_id']) && $payment_line['account_id'] == $id) selected @endif>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">{{ __('Payment Note') }}</label>
            <textarea name="payment[{{ $row_index }}][note]" class="form-control" rows="1" placeholder="{{ __('Payment notes...') }}">{{ $payment_line['note'] ?? '' }}</textarea>
        </div>
        @if(!empty($payment_line['id']))
            <input type="hidden" name="payment[{{ $row_index }}][payment_id]" value="{{ $payment_line['id'] }}">
        @endif
    </div>
</div>

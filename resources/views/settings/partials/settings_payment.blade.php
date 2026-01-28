<div class="row">
    <h4 class="card-title text-primary">Payment Settings</h4>
    <p class="text-muted">Configure payment default settings.</p>
    <div class="col-md-12">
        <div class="form-group mb-3">
            <label class="form-label">cash_denominations</label>
            <input type="text" name="pos_settings[cash_denominations]" class="form-control" id="cash_denominations"
                value="{{ $pos_settings['cash_denominations'] ?? '' }}">
            <small class="text-muted">cash_denominations_help</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">{{ __('Enable Denominations On') }}</label>
            <select name="pos_settings[enable_cash_denomination_on]" class="form-control select">
                <option value="pos_screen"
                    {{ ($pos_settings['enable_cash_denomination_on'] ?? 'pos_screen') == 'pos_screen' ? 'selected' : '' }}>
                    {{ __('POS Screen') }}</option>
                <option value="all_screens"
                    {{ ($pos_settings['enable_cash_denomination_on'] ?? '') == 'all_screens' ? 'selected' : '' }}>
                    {{ __('All Screens') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">{{ __('Enable for Payment Methods') }}</label>
            <select name="pos_settings[enable_cash_denomination_for_payment_methods][]" class="form-control form-select"
                multiple size="5">
                @php
                    $selected_methods = $pos_settings['enable_cash_denomination_for_payment_methods'] ?? [];
                @endphp
                @foreach (optional($payment_types) as $key => $val)
                    <option value="{{ $key }}" {{ in_array($key, $selected_methods) ? 'selected' : '' }}>
                        {{ $val }}</option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple options</small>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[cash_denomination_strict_check]"
                    id="cash_denomination_strict_check" value="1"
                    {{ !empty($pos_settings['cash_denomination_strict_check']) ? 'checked' : '' }}>
                <label class="form-check-label ms-2" for="cash_denomination_strict_check">{{ __('Strict Denomination Check') }}</label>
            </div>
        </div>
    </div>
</div>

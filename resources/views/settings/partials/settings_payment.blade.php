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
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">enable_cash_denomination_on</label>
            <select name="pos_settings[enable_cash_denomination_on]" class="form-control select">
                <option value="pos_screen"
                    {{ ($pos_settings['enable_cash_denomination_on'] ?? 'pos_screen') == 'pos_screen' ? 'selected' : '' }}>
                    pos_screen</option>
                <option value="all_screens"
                    {{ ($pos_settings['enable_cash_denomination_on'] ?? '') == 'all_screens' ? 'selected' : '' }}>
                    all_screen</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">enable_cash_denomination_for_payment_methods</label>
            <select name="pos_settings[enable_cash_denomination_for_payment_methods][]" class="form-control select"
                multiple>
                @php
                    $selected_methods = $pos_settings['enable_cash_denomination_for_payment_methods'] ?? [];
                @endphp
                {{-- @foreach (optional($payment_types) as $key => $val)
                    <option value="{{ $key }}" {{ in_array($key, $selected_methods) ? 'selected' : '' }}>
                        {{ $val }}</option>
                @endforeach --}}
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="cash_denomination_strict_check">strict_check</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[cash_denomination_strict_check]"
                    id="cash_denomination_strict_check" value="1"
                    {{ !empty($pos_settings['cash_denomination_strict_check']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>

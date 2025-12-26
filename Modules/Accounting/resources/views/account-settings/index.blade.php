<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Default Account Settings') }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('account-settings.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Default Sales Account') }}</label>
                            <select name="default_sales_account" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ isset($defaults['default_sales_account']) && $defaults['default_sales_account'] == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Default Payable Account') }}</label>
                            <select name="default_payable_account" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ isset($defaults['default_payable_account']) && $defaults['default_payable_account'] == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Default Receivable Account') }}</label>
                            <select name="default_receivable_account" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ isset($defaults['default_receivable_account']) && $defaults['default_receivable_account'] == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Default Bank Account') }}</label>
                            <select name="default_bank_account" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ isset($defaults['default_bank_account']) && $defaults['default_bank_account'] == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Default Cash Account') }}</label>
                            <select name="default_cash_account" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ isset($defaults['default_cash_account']) && $defaults['default_cash_account'] == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

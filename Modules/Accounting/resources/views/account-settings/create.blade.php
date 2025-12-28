<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">

        <form id="accountSettingsForm"
            method="POST"
            action="{{ isset($setting) 
                ? route('account-settings.update', $setting->id) 
                : route('account-settings.store') }}">

            @csrf
            @if (isset($setting))
                @method('PUT')
            @endif

            <div class="modal-body">

                {{-- DEFAULT ACCOUNTS --}}
                <h6 class="mb-3 text-primary">{{ __('Default Accounts') }}</h6>

                @php
                    $fields = [
                        'default_sales_account' => __('Default Sales Account'),
                        'default_payable_account' => __('Default Payable Account'),
                        'default_receivable_account' => __('Default Receivable Account'),
                        'default_bank_account' => __('Default Bank Account'),
                        'default_cash_account' => __('Default Cash Account'),
                    ];
                @endphp

                <div class="row">
                    @foreach ($fields as $key => $label)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ $label }}</label>
                            <select name="settings[{{ $key }}]" class="form-control select">
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach ($accounts as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ ($settings[$key] ?? '') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <hr>
                {{-- MANUAL ENTRY --}}
                <h6 class="mb-3 text-success">{{ __('Manual Entry') }}</h6>
                <input type="hidden" name="key" value="manual_entry">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ $setting->date ?? '' }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Account') }}</label>
                        <select name="account_id" class="form-control select" required>
                            @foreach ($accounts as $id => $name)
                                <option value="{{ $id }}"
                                    {{ ($setting->account_id ?? '') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Account Group') }}</label>
                        <select name="group_id" class="form-control select" required>
                            @foreach ($account_groups as $id => $name)
                                <option value="{{ $id }}"
                                    {{ ($setting->group_id ?? '') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Amount') }}</label>
                        <input type="number" step="0.01" name="amount"
                            class="form-control"
                            value="{{ $setting->amount ?? '' }}" required>
                    </div>
                </div>
                <input type="hidden" name="created_by" value="{{ auth()->id() }}">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ isset($setting) ? __('Update') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>

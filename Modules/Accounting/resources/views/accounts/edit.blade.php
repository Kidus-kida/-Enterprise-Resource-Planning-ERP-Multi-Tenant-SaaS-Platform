<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-white border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                    <i class="fa fa-edit"></i>
                </span>
                {{ __('accounting::lang.edit_account') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form
            action="{{ action([\Modules\Accounting\Http\Controllers\AccountController::class, 'update'], $account->id) }}"
            method="post" id="edit_account_form">
            @csrf
            @method('PUT')

            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Account Name -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.account_name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0"
                            required value="{{ $account->name }}"
                            placeholder="{{ __('accounting::lang.account_name') }}">
                    </div>

                    <!-- Account Type -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.account_type') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select form-select-lg bg-light border-0" name="account_type_id" required>
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach ($account_types as $account_type_id => $account_type_name)
                                <option value="{{ $account_type_id }}"
                                    @if ($account->account_type_id == $account_type_id) selected @endif>{{ $account_type_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Account Group (Asset Type) -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Account Group') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select form-select-lg bg-light border-0" name="asset_type" required>
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach ($account_groups as $account_group_id => $account_group_name)
                                <option value="{{ $account_group_id }}"
                                    @if ($account->asset_type == $account_group_id) selected @endif>{{ $account_group_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Account Number -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.account_number') }}</label>
                        <input type="text" name="account_number" class="form-control bg-light border-0"
                            value="{{ $account->account_number }}"
                            placeholder="{{ __('accounting::lang.account_number') }}">
                    </div>

                    <!-- Parent Account -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('Parent Account') }}</label>
                        <select class="form-select bg-light border-0" name="parent_account_id">
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach ($parent_accounts as $parent_account_id => $parent_account_name)
                                <option value="{{ $parent_account_id }}"
                                    @if ($account->parent_account_id == $parent_account_id) selected @endif>{{ $parent_account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Note -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">{{ __('accounting::lang.note') }}</label>
                        <textarea name="note" class="form-control bg-light border-0" rows="3"
                            placeholder="{{ __('accounting::lang.note') }}">{{ $account->note }}</textarea>
                    </div>

                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                <button type="button" class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fa fa-save me-1"></i> {{ __('messages.update') }}
                </button>
            </div>
        </form>
    </div>
</div>

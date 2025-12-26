<div class="modal-body">
    <form id="account_form" method="POST" action="{{ route('accounting.accounts.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Number') }}</label>
                    <input type="text" class="form-control" name="account_number" placeholder="Auto-generated if empty">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Type') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="account_type_id" required>
                        <option value="">-- {{ __('Select Type') }} --</option>
                        @foreach($accountTypes as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Group') }}</label>
                    <select class="form-control" name="asset_type">
                        <option value="">-- {{ __('Select') }} --</option>
                        @foreach($accountGroups as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Parent Account') }}</label>
                    <select class="form-control" name="parent_account_id">
                        <option value="">-- {{ __('None') }} --</option>
                        @foreach($parentAccounts as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Opening Balance') }}</label>
                    <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Note') }}</label>
                    <textarea class="form-control" name="note" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_main_account" id="is_main_account">
                    <label class="form-check-label" for="is_main_account">{{ __('Main Account') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="show_in_balance_sheet" id="show_in_balance_sheet" checked>
                    <label class="form-check-label" for="show_in_balance_sheet">{{ __('Show in Balance Sheet') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Cheque Required') }}</label>
                    <select class="form-control" name="is_need_cheque">
                        <option value="N">{{ __('No') }}</option>
                        <option value="Y">{{ __('Yes') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Save Account') }}</button>
        </div>
    </form>
</div>

<script>
    $('#account_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Account created successfully');
                    $('.modal').modal('hide');
                    $('#accounts_table').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message || 'Error creating account');
                }
            },
            error: function(xhr) {
                let message = 'Error creating account';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
</script>

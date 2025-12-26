<div class="modal-body">
    <form id="account_edit_form" method="POST" action="{{ route('accounting.accounts.update', $account->id) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ $account->name }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Account Number') }}</label>
                    <input type="text" class="form-control" name="account_number" value="{{ $account->account_number }}">
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
                            <option value="{{ $id }}" {{ $account->account_type_id == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                            <option value="{{ $id }}" {{ $account->asset_type == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Parent Account') }}</label>
                    <select class="form-control" name="parent_account_id">
                        <option value="">-- {{ __('None') }} --</option>
                        @foreach($parentAccounts as $id => $name)
                            <option value="{{ $id }}" {{ $account->parent_account_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Note') }}</label>
                    <textarea class="form-control" name="note" rows="3">{{ $account->note }}</textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_main_account" id="edit_is_main_account" {{ $account->is_main_account ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_is_main_account">{{ __('Main Account') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="show_in_balance_sheet" id="edit_show_in_balance_sheet" {{ $account->show_in_balance_sheet ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_show_in_balance_sheet">{{ __('Show in Balance Sheet') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Cheque Required') }}</label>
                    <select class="form-control" name="is_need_cheque">
                        <option value="N" {{ $account->is_need_cheque == 'N' ? 'selected' : '' }}>{{ __('No') }}</option>
                        <option value="Y" {{ $account->is_need_cheque == 'Y' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Update Account') }}</button>
        </div>
    </form>
</div>

<script>
    $('#account_edit_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Account updated successfully');
                    $('.modal').modal('hide');
                    $('#accounts_table').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message || 'Error updating account');
                }
            },
            error: function(xhr) {
                let message = 'Error updating account';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
</script>

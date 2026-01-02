<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      
        <form id="accountForm" method="POST" action="{{ isset($account) ? route('accounts.update', $account->id) : route('accounts.store') }}">
            @csrf
            @if(isset($account))
                @method('PUT')
            @endif
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Account Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $account->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Account Number') }}</label>
                            <input type="text" name="account_number" class="form-control" value="{{ $account->account_number ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Account Type') }} <span class="text-danger">*</span></label>
                            <select name="account_type_id" class="form-select" required>
                                <option value="">{{ __('Select Account Type') }}</option>
                                @foreach($account_types as $id => $name)
                                    <option value="{{ $id }}" {{ (isset($account) && $account->account_type_id == $id) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Account Group') }} <span class="text-danger">*</span></label>
                            <select name="asset_type" class="form-select" required>
                                <option value="">{{ __('Select Account Group') }}</option>
                                @foreach($account_groups as $id => $name)
                                    <option value="{{ $id }}" {{ (isset($account) && $account->asset_type == $id) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Parent Account') }}</label>
                            <select name="parent_account_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach($parent_accounts as $id => $name)
                                    <option value="{{ $id }}" {{ (isset($account) && $account->parent_account_id == $id) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Opening Balance') }}</label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ $account->opening_balance ?? '0' }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Requires Cheque') }}</label>
                            <select name="is_need_cheque" class="form-select">
                                <option value="N" {{ (isset($account) && $account->is_need_cheque == 'N') ? 'selected' : '' }}>{{ __('No') }}</option>
                                <option value="Y" {{ (isset($account) && $account->is_need_cheque == 'Y') ? 'selected' : '' }}>{{ __('Yes') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_main_account" class="form-check-input" id="is_main_account" value="1" {{ (isset($account) && $account->is_main_account) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_main_account">
                                    {{ __('Is Main Account') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Note') }}</label>
                    <textarea name="note" class="form-control" rows="3">{{ $account->note ?? '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ isset($account) ? __('Update Account') : __('Create Account') }}</button>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script type="module">
    $('#accountForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('.modal').modal('hide');
                    $('.datatable').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred');
            }
        });
    });
</script>
@endpush

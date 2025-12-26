<div class="modal-body">
    <form id="account_group_form" method="POST" action="{{ route('accounting.account-groups.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Group Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
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
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Note') }}</label>
                    <textarea class="form-control" name="note" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Save Group') }}</button>
        </div>
    </form>
</div>

<script>
    $('#account_group_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Account Group created successfully');
                    $('.modal').modal('hide');
                    $('#account_groups_table').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message || 'Error creating account group');
                }
            },
            error: function(xhr) {
                let message = 'Error creating account group';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
</script>

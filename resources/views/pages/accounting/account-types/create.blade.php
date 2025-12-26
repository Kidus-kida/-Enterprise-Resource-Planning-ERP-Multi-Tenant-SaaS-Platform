<div class="modal-body">
    <form id="account_type_form" method="POST" action="{{ route('accounting.account-types.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Type Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Parent Type') }}</label>
                    <select class="form-control" name="parent_account_type_id">
                        <option value="">-- {{ __('None (Main Type)') }} --</option>
                        @foreach($parentTypes as $id => $name)
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
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Save Type') }}</button>
        </div>
    </form>
</div>

<script>
    $('#account_type_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Account Type created successfully');
                    $('.modal').modal('hide');
                    $('#account_types_table').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message || 'Error creating account type');
                }
            },
            error: function(xhr) {
                let message = 'Error creating account type';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
</script>

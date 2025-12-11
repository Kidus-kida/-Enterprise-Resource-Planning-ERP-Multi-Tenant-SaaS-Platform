<div class="modal-body">
    <form id="account_type_edit_form" method="POST" action="{{ route('accounting.account-types.update', $type->id) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Type Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ $type->name }}" required>
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
                            <option value="{{ $id }}" {{ $type->parent_account_type_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Note') }}</label>
                    <textarea class="form-control" name="note" rows="3">{{ $type->note }}</textarea>
                </div>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Update Type') }}</button>
        </div>
    </form>
</div>

<script>
    $('#account_type_edit_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Account Type updated successfully');
                    $('.modal').modal('hide');
                    $('#account_types_table').DataTable().ajax.reload();
                } else {
                    toastr.error(response.message || 'Error updating account type');
                }
            },
            error: function(xhr) {
                let message = 'Error updating account type';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
</script>

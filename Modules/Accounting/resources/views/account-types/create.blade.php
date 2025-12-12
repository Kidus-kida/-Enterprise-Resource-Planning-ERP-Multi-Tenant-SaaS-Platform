<div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ isset($type) ? __('Edit Account Type') : __('Add Account Type') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="typeForm" method="POST" action="{{ isset($type) ? route('account-types.update', $type->id) : route('account-types.store') }}">
            @csrf
            @if(isset($type))
                @method('PUT')
            @endif
            
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $type->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ $type->description ?? '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ isset($type) ? __('Update') : __('Create') }}</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
    $('#typeForm').submit(function(e) {
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

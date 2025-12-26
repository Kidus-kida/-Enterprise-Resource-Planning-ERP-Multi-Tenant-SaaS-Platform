<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-white border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                <span class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-2">
                    <i class="fa fa-sticky-note"></i>
                </span>
                {{ __('accounting::lang.account_notes') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('account.post-notes', $account->id) }}" method="post" id="account_notes_form">
            @csrf
            <input type="hidden" name="account_id" value="{{ $account->id }}">

            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="alert alert-light border shadow-sm rounded-3 mb-3 d-flex align-items-center">
                        <i class="fa fa-info-circle text-primary me-2 fs-5"></i>
                        <div>
                            <strong>{{ $account->name }}</strong> <span
                                class="text-muted">({{ $account->account_number }})</span>
                        </div>
                    </div>

                    <label class="form-label fw-semibold">{{ __('accounting::lang.note') }}</label>
                    <textarea name="note" class="form-control form-control-lg bg-light border-0" rows="5"
                        placeholder="{{ __('Add notes here...') }}">{{ $account->note }}</textarea>
                    <div class="form-text">{{ __('These notes are internal and visible to admins only.') }}</div>
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

<script>
    $(document).ready(function() {
        $('#account_notes_form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg);
                        $('#commonModal').modal('hide');
                        $('#accounts_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.msg);
                    }
                }
            });
        });
    });
</script>

<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-0 pb-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
            @if (isset($file_path) && !empty($file_path))
                <img src="{{ $file_path }}" class="img-fluid rounded-4 w-100" alt="Attachment">
            @else
                <div class="text-center py-5">
                    <i class="fa fa-image fa-3x text-muted mb-3"></i>
                    <p class="text-muted">{{ __('No image available') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

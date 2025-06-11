
<div class="modal-body">
    <form action="" enctype="multipart/form-data">
        @csrf

        <x-form.input-block>
            <x-form.label for="files">{{ __('Upload Files') }}</x-form.label>
            <input type="file" name="files[]" id="files" class="form-control" multiple>
            <small class="form-text text-muted">You can select multiple files (images, PDFs, etc).</small>
        </x-form.input-block>

        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>


<div class="modal-body">
    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
        @csrf
        <input type="hidden" name="folder_id" value="{{ $folder_id }}">

        <div class="mb-3">
            <label for="files" class="form-label">Upload Files</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple required>
            <div class="form-text">Max 10MB per file</div>
        </div>

        <button type="submit" class="btn btn-primary" id="upload-button">
            <span class="button-text">Upload</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </form>
</div>

<script>
    document.getElementById('upload-form').addEventListener('submit', function() {
        const button = document.getElementById('upload-button');
        button.disabled = true;
        button.querySelector('.button-text').textContent = 'Uploading...';
        button.querySelector('.spinner-border').classList.remove('d-none');
    });
</script>
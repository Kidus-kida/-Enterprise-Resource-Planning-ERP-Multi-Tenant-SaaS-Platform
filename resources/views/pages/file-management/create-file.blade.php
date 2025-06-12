
<div class="modal-body">
    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="folder_id" value="{{ $folder_id }}">

        <div class="mb-3">
            <label for="files" class="form-label">Upload Files</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple required>
            <div class="form-text">Max 10MB per file</div>
        </div>

        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
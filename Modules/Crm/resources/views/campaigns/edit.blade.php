<form action="{{ route('campaigns.update', $campaign) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ $campaign->title }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Start Date') }}</label>
                <input type="date" name="start_date" class="form-control" value="{{ $campaign->start_date }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('End Date') }}</label>
                <input type="date" name="end_date" class="form-control" value="{{ $campaign->end_date }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="draft" {{ $campaign->status == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                    <option value="active" {{ $campaign->status == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="paused" {{ $campaign->status == 'paused' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                    <option value="completed" {{ $campaign->status == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="3">{{ $campaign->description }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
</form>
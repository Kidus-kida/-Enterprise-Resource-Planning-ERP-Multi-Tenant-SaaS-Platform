<form action="{{ route('campaigns.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Start Date') }}</label>
                <input type="date" name="start_date" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('End Date') }}</label>
                <input type="date" name="end_date" class="form-control">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="draft">{{ __('Draft') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="paused">{{ __('Paused') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </div>
</form>
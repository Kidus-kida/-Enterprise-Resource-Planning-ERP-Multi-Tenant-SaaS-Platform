<form action="{{ route('leads.store') }}" method="POST">
    @csrf
   <div class="modal-body p-3">
        <div class="row g-3">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Email') }}</label>
                <input type="email" name="email" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Phone') }}</label>
                <input type="text" name="phone" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Company') }}</label>
                <input type="text" name="company" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="new">{{ __('New') }}</option>
                    <option value="contacted">{{ __('Contacted') }}</option>
                    <option value="qualified">{{ __('Qualified') }}</option>
                    <option value="converted">{{ __('Converted') }}</option>
                    <option value="lost">{{ __('Lost') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Source') }}</label>
                <input type="text" name="source" class="form-control">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </div>
   </div>
</form>

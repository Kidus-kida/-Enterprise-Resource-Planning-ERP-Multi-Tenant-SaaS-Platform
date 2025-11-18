<form action="{{ route('leads.update', $lead) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $lead->name }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Email') }}</label>
                <input type="email" name="email" class="form-control" value="{{ $lead->email }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ $lead->phone }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Company') }}</label>
                <input type="text" name="company" class="form-control" value="{{ $lead->company }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>{{ __('New') }}</option>
                    <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>{{ __('Contacted') }}</option>
                    <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>{{ __('Qualified') }}</option>
                    <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>{{ __('Converted') }}</option>
                    <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>{{ __('Lost') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Source') }}</label>
                <input type="text" name="source" class="form-control" value="{{ $lead->source }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="3">{{ $lead->notes }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
</form>
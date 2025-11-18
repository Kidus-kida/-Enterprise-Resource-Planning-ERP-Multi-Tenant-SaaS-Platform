<form action="{{ route('follow-ups.update', $followUp) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Lead') }} <span class="text-danger">*</span></label>
                <select name="lead_id" class="form-control" required>
                    <option value="">{{ __('Select Lead') }}</option>
                    @foreach($leads as $lead)
                    <option value="{{ $lead->id }}" {{ $followUp->lead_id == $lead->id ? 'selected' : '' }}>
                        {{ $lead->name }} - {{ $lead->company }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ $followUp->title }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Follow-up Date') }} <span class="text-danger">*</span></label>
                <input type="datetime-local" name="follow_up_date" class="form-control" 
                       value="{{ $followUp->follow_up_date->format('Y-m-d\TH:i') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="pending" {{ $followUp->status == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="completed" {{ $followUp->status == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ $followUp->status == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Assigned To') }}</label>
                <select name="assigned_to" class="form-control">
                    <option value="">{{ __('Select User') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $followUp->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea name="description" class="form-control" rows="3">{{ $followUp->description }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
</form>
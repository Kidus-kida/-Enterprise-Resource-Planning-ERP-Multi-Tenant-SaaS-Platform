@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header">
            <h4><i class="fa fa-plus"></i> {{ __('Create Mandatory Day') }}</h4>
        </div>
        
        <form action="{{ route('leave.config.mandatory-days.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Annual Audit, Company Retreat">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Restriction Type') }} <span class="text-danger">*</span></label>
                    <select name="restriction_type" class="form-select" required onchange="updateMessagePlaceholder(this)">
                        <option value="no_leave" {{ old('restriction_type') == 'no_leave' ? 'selected' : '' }}>{{ __('Strict - No Leave Allowed') }}</option>
                        <option value="requires_approval" {{ old('restriction_type') == 'requires_approval' ? 'selected' : '' }}>{{ __('Soft - Requires Approval') }}</option>
                        <option value="warning_only" {{ old('restriction_type') == 'warning_only' ? 'selected' : '' }}>{{ __('Info - Warning Only') }}</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Restriction Message') }}</label>
                    <input type="text" name="restriction_message" id="restriction_message" class="form-control" value="{{ old('restriction_message') }}" placeholder="e.g. Leave not allowed due to...">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">{{ __('Applicability') }}</label>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small text-muted mb-1">{{ __('Specific Departments (Empty = All)') }}</label>
                            <select name="applicable_departments[]" class="form-control select" multiple>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (collect(old('applicable_departments'))->contains($dept->id)) ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted mb-1">{{ __('Specific Designations (Empty = All)') }}</label>
                            <select name="applicable_designations[]" class="form-control select" multiple>
                                @foreach($designations as $desig)
                                    <option value="{{ $desig->id }}" {{ (collect(old('applicable_designations'))->contains($desig->id)) ? 'selected' : '' }}>
                                        {{ $desig->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('leave.config.mandatory-days.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">{{ __('Save Mandatory Day') }}</button>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
    function updateMessagePlaceholder(select) {
        const input = document.getElementById('restriction_message');
        if (select.value === 'no_leave') {
            input.placeholder = "{{ __('e.g. No leave requests allowed on this day.') }}";
        } else if (select.value === 'requires_approval') {
            input.placeholder = "{{ __('e.g. Leave requests require managerial approval.') }}";
        } else {
            input.placeholder = "{{ __('e.g. Please be aware of this important date.') }}";
        }
    }
</script>
@endpush
@endsection

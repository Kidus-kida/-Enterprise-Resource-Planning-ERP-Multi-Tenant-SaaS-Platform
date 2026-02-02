@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header">
            <h4><i class="fa fa-plus"></i> {{ __('Create Time Off Type') }}</h4>
        </div>
        
        <form action="{{ route('leave.config.time-off-types.store') }}" method="POST">
            @csrf
            
            <div class="row">
                {{-- Basic Information --}}
                <div class="col-md-12 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Basic Information') }}</h5>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="type_name" class="form-control" value="{{ old('type_name') }}" required placeholder="e.g. Annual Leave, Sick Leave">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('Color') }}</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', '#0d6efd') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('Paid Time Off') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_paid">{{ __('Yes, it is paid') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Allocation & Accrual --}}
                <div class="col-md-12 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Allocation Mode') }}</h5>
                    <div class="row mt-3">
                        <div class="col-md-12 mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="uses_accrual" id="mode_fixed" value="0" {{ old('uses_accrual') == '0' ? 'checked' : 'checked' }} onchange="toggleAllocationMode()">
                                <label class="btn btn-outline-primary" for="mode_fixed">
                                    <i class="fa fa-hand-holding"></i> {{ __('Fixed Allocation (Manual)') }}
                                </label>

                                <input type="radio" class="btn-check" name="uses_accrual" id="mode_accrual" value="1" {{ old('uses_accrual') == '1' ? 'checked' : '' }} onchange="toggleAllocationMode()">
                                <label class="btn btn-outline-primary" for="mode_accrual">
                                    <i class="fa fa-chart-line"></i> {{ __('Accrual Plan (Automatic)') }}
                                </label>
                            </div>
                        </div>

                        {{-- Fixed Mode Fields --}}
                        <div class="col-md-6 mb-3 mode-fixed-field">
                            <label class="form-label">{{ __('Default Days Allowed (Per Year)') }}</label>
                            <input type="number" name="max_date_allowed" class="form-control" value="{{ old('max_date_allowed', 0) }}" min="0">
                            <small class="text-muted">{{ __('Default number of days allocated manually') }}</small>
                        </div>

                        {{-- Accrual Mode Fields --}}
                        <div class="col-md-6 mb-3 mode-accrual-field" style="display: none;">
                            <label class="form-label">{{ __('Accrual Plan') }}</label>
                            <select name="default_accrual_plan_id" class="form-select">
                                <option value="">{{ __('Select Accrual Plan') }}</option>
                                @foreach($accrualPlans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('default_accrual_plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Validation & Rules') }}</h5>
                    <div class="mt-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="requires_attachment" id="requires_attachment" value="1" {{ old('requires_attachment') ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_attachment">{{ __('Requires Attachment (e.g. Medical Certificate)') }}</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="allow_half_day" id="allow_half_day" value="1" {{ old('allow_half_day', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_half_day">{{ __('Allow Half Days') }}</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Minimum Notice Period (Days)') }}</label>
                            <input type="number" name="min_days_notice" class="form-control" value="{{ old('min_days_notice', 0) }}" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Max Consecutive Days') }}</label>
                            <input type="number" name="max_consecutive_days" class="form-control" value="{{ old('max_consecutive_days') }}" min="0" placeholder="e.g. 15">
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Approval Workflow') }}</h5>
                    <div class="mt-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="requires_approval" id="requires_approval" value="1" {{ old('requires_approval', true) ? 'checked' : '' }} onchange="toggleApprovalFields()">
                            <label class="form-check-label" for="requires_approval">{{ __('Requires Approval') }}</label>
                        </div>

                        <div id="approval_fields">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Approval Levels') }}</label>
                                <select name="approval_levels" class="form-select">
                                    <option value="1" {{ old('approval_levels') == '1' ? 'selected' : '' }}>{{ __('1 Level (Manager)') }}</option>
                                    <option value="2" {{ old('approval_levels') == '2' ? 'selected' : '' }}>{{ __('2 Levels (Manager + HR)') }}</option>
                                    <option value="3" {{ old('approval_levels') == '3' ? 'selected' : '' }}>{{ __('3 Levels (Manager + HR + Director)') }}</option>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="auto_approve_if_balance" id="auto_approve_if_balance" value="1" {{ old('auto_approve_if_balance') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_approve_if_balance">{{ __('Auto-approve if sufficient balance') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('leave.config.time-off-types.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">{{ __('Save Time Off Type') }}</button>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
    function toggleAllocationMode() {
        const isAccrual = document.getElementById('mode_accrual').checked;
        if (isAccrual) {
            $('.mode-accrual-field').show();
            $('.mode-fixed-field').hide();
        } else {
            $('.mode-accrual-field').hide();
            $('.mode-fixed-field').show();
        }
    }

    function toggleApprovalFields() {
        const required = document.getElementById('requires_approval').checked;
        if (required) {
            $('#approval_fields').slideDown();
        } else {
            $('#approval_fields').slideUp();
        }
    }

    // Initialize state
    document.addEventListener('DOMContentLoaded', function() {
        toggleAllocationMode();
        toggleApprovalFields();
    });
</script>
@endpush
@endsection

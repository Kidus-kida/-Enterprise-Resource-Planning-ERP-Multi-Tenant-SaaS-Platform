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
            
            <div class="leave-card-body p-3">
                {{-- Basic Information --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Type Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="type_name" class="form-control" value="{{ old('type_name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Code / Color') }}</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', '#0d6efd') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Sort Order') }}</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="col-md-12 mt-2">
                         <label class="form-label">{{ __('Description') }}</label>
                         <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Main Configuration Tabs --}}
                <ul class="nav nav-tabs nav-tabs-bottom mb-3" id="timeOffTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="logic-tab" data-bs-toggle="tab" data-bs-target="#logic" type="button" role="tab">{{ __('Time Off Logic') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="allocation-tab" data-bs-toggle="tab" data-bs-target="#allocation" type="button" role="tab">{{ __('Allocation Requests') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" type="button" role="tab">{{ __('Display & Rules') }}</button>
                    </li>
                </ul>

                <div class="tab-content border p-3 rounded bg-white">
                    
                    {{-- Tab 1: Time Off Logic --}}
                    <div class="tab-pane fade show active" id="logic" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Duration Type') }}</label>
                                <select name="duration_type" class="form-select">
                                    <option value="day" {{ old('duration_type') == 'day' ? 'selected' : '' }}>{{ __('Day') }}</option>
                                    <option value="half_day" {{ old('duration_type') == 'half_day' ? 'selected' : '' }}>{{ __('Half Day') }}</option>
                                    <option value="hours" {{ old('duration_type') == 'hours' ? 'selected' : '' }}>{{ __('Hours') }}</option>
                                </select>
                                <small class="text-muted">{{ __('How the duration is calculated.') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Count As') }}</label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="count_as" id="count_absence" value="absence" {{ old('count_as', 'absence') == 'absence' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="count_absence">{{ __('Absence') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="count_as" id="count_worked" value="worked_time" {{ old('count_as') == 'worked_time' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="count_worked">{{ __('Worked Time') }}</label>
                                    </div>
                                </div>
                                <small class="text-muted">{{ __('Used for accrual computation.') }}</small>
                            </div>

                            <div class="col-md-12 mt-3">
                                <h5 class="text-primary mb-3 border-bottom pb-2">{{ __('Approval Workflow') }}</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch pt-2">
                                            <input class="form-check-input" type="checkbox" name="requires_approval" id="requires_approval" value="1" {{ old('requires_approval', 1) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="requires_approval">{{ __('Requires Approval') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 approval-levels-div">
                                        <label class="form-label">{{ __('Approval Levels') }}</label>
                                        <select name="approval_levels" class="form-select">
                                            <option value="1" {{ old('approval_levels') == 1 ? 'selected' : '' }}>{{ __('1 Level (Manager)') }}</option>
                                            <option value="2" {{ old('approval_levels') == 2 ? 'selected' : '' }}>{{ __('2 Levels (Manager + HR)') }}</option>
                                            <option value="3" {{ old('approval_levels') == 3 ? 'selected' : '' }}>{{ __('3 Levels (Man + HR + Dir)') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch pt-2">
                                            <input class="form-check-input" type="checkbox" name="notify_hr" id="notify_hr" value="1" {{ old('notify_hr') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_hr">{{ __('Notify HR') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3 hr-recipients-div" style="display: none;">
                                        <label class="form-label fw-bold">{{ __('HR Notification Recipients') }}</label>
                                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                            {{-- Roles Section --}}
                                            <div class="mb-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="select_all_roles">
                                                    <label class="form-check-label fw-bold" for="select_all_roles">
                                                        {{ __('Select All Roles') }}
                                                    </label>
                                                </div>
                                                <div class="ms-3">
                                                    @foreach($roles as $role)
                                                        <div class="form-check">
                                                            <input class="form-check-input hr-role-checkbox" type="checkbox" name="hr_notification_recipients[]" value="role_{{ $role->id }}" id="role_{{ $role->id }}" {{ in_array('role_'.$role->id, old('hr_notification_recipients', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                                {{ $role->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <hr>
                                            
                                            {{-- Users Section --}}
                                            <div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="select_all_users">
                                                    <label class="form-check-label fw-bold" for="select_all_users">
                                                        {{ __('Select All Users') }}
                                                    </label>
                                                </div>
                                                <div class="ms-3">
                                                    @foreach($users as $user)
                                                        <div class="form-check">
                                                            <input class="form-check-input hr-user-checkbox" type="checkbox" name="hr_notification_recipients[]" value="user_{{ $user->id }}" id="user_{{ $user->id }}" {{ in_array('user_'.$user->id, old('hr_notification_recipients', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="user_{{ $user->id }}">
                                                                {{ $user->firstname }} {{ $user->lastname }} <span class="text-muted">({{ $user->email }})</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ __('Select roles and/or specific users to notify when this leave type is requested.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Allocation Requests --}}
                    <div class="tab-pane fade" id="allocation" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> {{ __('These settings control how allocations (leave balances) are requested and approved.') }}
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" name="requires_allocation" id="requires_allocation" value="1" {{ old('requires_allocation', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="requires_allocation">{{ __('Requires Allocation') }}</label>
                                    <small class="text-muted d-block mt-1">{{ __('If unchecked, users can request this leave without a preset balance (limitless).') }}</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3 allocation-settings">
                                <div class="form-check form-switch p-3 border rounded bg-light">
                                    <input class="form-check-input" type="checkbox" name="employee_requests_allowed" id="employee_requests_allowed" value="1" {{ old('employee_requests_allowed') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="employee_requests_allowed">{{ __('Allow Employee Requests') }}</label>
                                    <small class="text-muted d-block mt-1">{{ __('Users can request allocations for themselves (Extra Days).') }}</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 allocation-settings">
                                <label class="form-label">{{ __('Allocation Approval Levels') }}</label>
                                <select name="allocation_approval_levels" class="form-select">
                                    <option value="1" {{ old('allocation_approval_levels') == 1 ? 'selected' : '' }}>{{ __('1 Level (Manager)') }}</option>
                                    <option value="2" {{ old('allocation_approval_levels') == 2 ? 'selected' : '' }}>{{ __('2 Levels (Manager + HR)') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Display & Configuration --}}
                    <div class="tab-pane fade" id="display" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-muted mb-3 border-bottom pb-2">{{ __('Visibility') }}</h5>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Max Days Allowed (Per Year)') }}</label>
                                    <input type="number" name="max_date_allowed" class="form-control" value="{{ old('max_date_allowed', 0) }}" min="0">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="hide_on_dashboard" id="hide_on_dashboard" value="1" {{ old('hide_on_dashboard') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hide_on_dashboard">{{ __('Hide on Dashboard') }}</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ignore_public_holidays" id="ignore_public_holidays" value="1" {{ old('ignore_public_holidays') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ignore_public_holidays">{{ __('Ignore Public Holidays') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-muted mb-3 border-bottom pb-2">{{ __('Accounting & Balances') }}</h5>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="eligible_for_accrual" id="eligible_for_accrual" value="1" {{ old('eligible_for_accrual') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="eligible_for_accrual">{{ __('Eligible for Accrual Rate') }}</label>
                                    </div>
                                    <small class="text-muted d-block ms-4">{{ __('Taken into account for accruals computation.') }}</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allow_negative_balance" id="allow_negative_balance" value="1" {{ old('allow_negative_balance') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_negative_balance">{{ __('Allow Negative Cap') }}</label>
                                    </div>
                                    <div class="mt-2 ms-4 negative-cap-div" style="display: none;">
                                        <label class="form-label mb-1" id="max-negative-label">{{ __('Max Negative Days') }}</label>
                                        <input type="number" name="max_negative_balance" class="form-control form-control-sm" style="width: 100px;" value="{{ old('max_negative_balance', 0) }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="can_carry_forward" id="can_carry_forward" value="1" {{ old('can_carry_forward') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="can_carry_forward">{{ __('Allow Carry Forward') }}</label>
                                    </div>
                                    <div class="mt-2 ms-4 carry-forward-div" style="display: none;">
                                        <div class="mb-2">
                                            <label class="form-label mb-1">{{ __('Max Carry Forward Days') }}</label>
                                            <input type="number" name="max_carry_forward" class="form-control form-control-sm" style="width: 100px;" value="{{ old('max_carry_forward', 0) }}">
                                            <small class="text-muted d-block">{{ __('how many days can be rolled over') }}</small>
                                        </div>
                                        <div>
                                            <label class="form-label mb-1">{{ __('Expiry (Months)') }}</label>
                                            <input type="number" name="carry_forward_expiry" class="form-control form-control-sm" style="width: 100px;" value="{{ old('carry_forward_expiry') }}" placeholder="e.g. 3">
                                            <small class="text-muted d-block">{{ __('Months after year-end') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_paid">{{ __('Is Paid Leave') }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <h5 class="text-muted mb-3 border-bottom pb-2">{{ __('Validations') }}</h5>
                                <div class="row">
                                     <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requires_attachment" id="requires_attachment" value="1" {{ old('requires_attachment') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="requires_attachment">{{ __('Requires Attachment') }}</label>
                                        </div>
                                    </div>
                                     <div class="col-md-4 mb-3 allow-half-day-div">
                                         <div class="form-check">
                                             <input class="form-check-input" type="checkbox" name="allow_half_day" id="allow_half_day" value="1" {{ old('allow_half_day', 1) ? 'checked' : '' }}>
                                             <label class="form-check-label" for="allow_half_day">{{ __('Allow Half Days') }}</label>
                                         </div>
                                     </div>
                                     <div class="col-md-4 mb-3">
                                         <label class="form-label">{{ __('Max Consecutive Days') }}</label>
                                         <input type="number" name="max_consecutive_days" class="form-control" value="{{ old('max_consecutive_days') }}" min="1" placeholder="e.g., 14">
                                         <small class="text-muted">{{ __('Maximum days in a single request') }}</small>
                                     </div>
                                 </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="submit-section mt-4 text-end">
                    <a href="{{ route('leave.config.time-off-types.index') }}" class="btn btn-secondary me-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary submit-btn px-4">{{ __('Save Time Off Type') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamic Duration Type Label Update
        const durationTypeSelect = document.querySelector('select[name="duration_type"]');
        const maxNegativeLabel = document.getElementById('max-negative-label');
        
        function updateNegativeLabel() {
            if (durationTypeSelect && maxNegativeLabel) {
                const durationType = durationTypeSelect.value;
                let labelText = 'Max Negative Days'; // Default
                
                if (durationType === 'hours') {
                    labelText = 'Max Negative Hours';
                } else if (durationType === 'half_day') {
                    labelText = 'Max Negative Half Days';
                } else {
                    labelText = 'Max Negative Days';
                }
                
                maxNegativeLabel.textContent = labelText;
            }
        }
        
        if (durationTypeSelect) {
            durationTypeSelect.addEventListener('change', updateNegativeLabel);
            updateNegativeLabel(); // Set initial state
        }
        
        // Allow Half Days Visibility Toggle (show only for Duration Type = "day")
        const allowHalfDayDiv = document.querySelector('.allow-half-day-div');
        
        function toggleAllowHalfDay() {
            if (durationTypeSelect && allowHalfDayDiv) {
                const durationType = durationTypeSelect.value;
                if (durationType === 'day') {
                    allowHalfDayDiv.style.display = 'block';
                } else {
                    allowHalfDayDiv.style.display = 'none';
                }
            }
        }
        
        if (durationTypeSelect) {
            durationTypeSelect.addEventListener('change', toggleAllowHalfDay);
            toggleAllowHalfDay(); // Set initial state
        }

        // Carry Forward Toggle
        const canCarryForward = document.getElementById('can_carry_forward');
        const carryForwardDiv = document.querySelector('.carry-forward-div');
        
        function toggleCarryForward() {
            if(canCarryForward && carryForwardDiv) {
                if(canCarryForward.checked) {
                    carryForwardDiv.style.display = 'block';
                } else {
                    carryForwardDiv.style.display = 'none';
                }
            }
        }
        if(canCarryForward) {
            canCarryForward.addEventListener('change', toggleCarryForward);
            toggleCarryForward();
        }
        
        // Approval Toggle
        const requiresApproval = document.getElementById('requires_approval');
        const approvalLevelsDiv = document.querySelector('.approval-levels-div');
        
        function toggleApproval() {
             if(requiresApproval && approvalLevelsDiv) {
                if(requiresApproval.checked) {
                    approvalLevelsDiv.style.opacity = '1';
                    approvalLevelsDiv.style.pointerEvents = 'auto';
                } else {
                    approvalLevelsDiv.style.opacity = '0.5';
                    approvalLevelsDiv.style.pointerEvents = 'none';
                }
            }
        }
        if(requiresApproval) {
            requiresApproval.addEventListener('change', toggleApproval);
            toggleApproval();
        }

        // Negative Cap Toggle
        const allowNegative = document.getElementById('allow_negative_balance');
        const negativeCapDiv = document.querySelector('.negative-cap-div');

        function toggleNegative() {
            if(allowNegative && negativeCapDiv) {
                if(allowNegative.checked) {
                    negativeCapDiv.style.display = 'block';
                } else {
                    negativeCapDiv.style.display = 'none';
                }
            }
        }
        if(allowNegative) {
            allowNegative.addEventListener('change', toggleNegative);
            toggleNegative();
        }
        
        // Allocation Settings Toggle
        const requiresAllocation = document.getElementById('requires_allocation');
        const allocationSettings = document.querySelectorAll('.allocation-settings');
        
        function toggleAllocation() {
             if(requiresAllocation) {
                 allocationSettings.forEach(el => {
                     el.style.opacity = requiresAllocation.checked ? '1' : '0.5';
                     el.style.pointerEvents = requiresAllocation.checked ? 'auto' : 'none';
                 });
             }
        }
        if(requiresAllocation) {
            requiresAllocation.addEventListener('change', toggleAllocation);
            toggleAllocation();
        }
        
        // HR Recipients Toggle
        const notifyHr = document.getElementById('notify_hr');
        const hrRecipientsDiv = document.querySelector('.hr-recipients-div');
        
        function toggleHrRecipients() {
            if(notifyHr && hrRecipientsDiv) {
                if(notifyHr.checked) {
                    hrRecipientsDiv.style.display = 'block';
                } else {
                    hrRecipientsDiv.style.display = 'none';
                }
            }
        }
        if(notifyHr) {
            notifyHr.addEventListener('change', toggleHrRecipients);
            toggleHrRecipients();
        }
        
        // Select All Roles
        const selectAllRoles = document.getElementById('select_all_roles');
        const roleCheckboxes = document.querySelectorAll('.hr-role-checkbox');
        
        if(selectAllRoles) {
            selectAllRoles.addEventListener('change', function() {
                roleCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllRoles.checked;
                });
            });
        }
        
        // Select All Users
        const selectAllUsers = document.getElementById('select_all_users');
        const userCheckboxes = document.querySelectorAll('.hr-user-checkbox');
        
        if(selectAllUsers) {
            selectAllUsers.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllUsers.checked;
                });
            });
        }
    });
</script>
@endpush
@endsection

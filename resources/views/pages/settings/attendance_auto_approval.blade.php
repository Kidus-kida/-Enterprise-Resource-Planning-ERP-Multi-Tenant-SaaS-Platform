@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ $pageTitle }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendance-settings.index') }}">{{ __('Attendance Settings') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Auto-Approval') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <form action="{{ route('admin.attendance-settings.auto-approval.update') }}" method="POST" id="auto-approval-form">
            @csrf
            @method('POST')
            
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <i class="la la-magic text-primary" style="font-size: 1.5rem;"></i>
                                    {{ __('Auto-Approval Configuration') }}
                                </h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.attendance-settings.index') }}" class="btn btn-outline-secondary btn-sm px-4">
                                        <i class="la la-arrow-left"></i> {{ __('Back to Settings') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-sm px-4">
                                        <i class="la la-save"></i> {{ __('Save Configuration') }}
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-info border-0 bg-info-light mt-0 mb-0 d-flex align-items-start gap-2">
                                <i class="la la-lightbulb text-info fs-5 mt-1"></i>
                                <div class="small text-info-emphasis lh-sm">
                                    {{ __('Auto-approval streamlines routine overtime requests by automatically approving them when predefined criteria are met, reducing approval bottlenecks while maintaining oversight.') }}
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Auto-Approval Configuration -->
                            <div id="auto_approval_config">
                                <div class="row g-4">
                                    <!-- Approval Criteria -->
                                    <div class="col-md-6">
                                        <div class="card h-100 border shadow-none bg-light-subtle">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                                    <i class="la la-filter me-1"></i> {{ __('Approval Criteria') }}
                                                </h6>

                                                <!-- Hours Threshold -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">{{ __('Maximum Hours per Request') }}</label>
                                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                                        <input type="number" name="max_hours_per_request" class="form-control" value="{{ $settings['max_hours_per_request'] }}" min="0" max="24" step="0.5">
                                                        <span class="input-group-text">{{ __('hours') }}</span>
                                                    </div>
                                                    <p class="extra-small text-muted mb-0 mt-1">{{ __('Auto-approve if OT hours ≤ this value') }}</p>
                                                </div>

                                                <!-- Advance Notice -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">{{ __('Minimum Advance Notice') }}</label>
                                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                                        <input type="number" name="min_advance_notice_hours" class="form-control" value="{{ $settings['min_advance_notice_hours'] }}" min="0" max="168">
                                                        <span class="input-group-text">{{ __('hours') }}</span>
                                                    </div>
                                                    <p class="extra-small text-muted mb-0 mt-1">{{ __('Auto-approve if requested ≥ this many hours in advance') }}</p>
                                                </div>

                                                <!-- Monthly Limit -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">{{ __('Monthly Limit per Employee') }}</label>
                                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                                        <input type="number" name="monthly_limit_per_employee" class="form-control" value="{{ $settings['monthly_limit_per_employee'] }}" min="0" max="100">
                                                        <span class="input-group-text">{{ __('hours') }}</span>
                                                    </div>
                                                    <p class="extra-small text-muted mb-0 mt-1">{{ __('Maximum auto-approved OT hours per employee per month') }}</p>
                                                </div>

                                                <!-- Additional Conditions -->
                                                <div class="mb-0">
                                                    <label class="form-label small fw-bold mb-2">{{ __('Additional Conditions') }}</label>
                                                    <div class="d-flex flex-column gap-2 ms-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="require_attendance_records" value="1" id="require_attendance" {{ $settings['require_attendance_records'] ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="require_attendance">
                                                                {{ __('Require valid attendance records') }}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="check_budget_availability" value="1" id="check_budget" {{ $settings['check_budget_availability'] ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="check_budget">
                                                                {{ __('Check department budget availability') }}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="verify_policy_compliance" value="1" id="check_compliance" {{ $settings['verify_policy_compliance'] ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="check_compliance">
                                                                {{ __('Verify no recent policy violations') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fallback Behavior & Restrictions -->
                                    <div class="col-md-6">
                                        <div class="card h-100 border shadow-none bg-light-subtle">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                                    <i class="la la-route me-1"></i> {{ __('Fallback & Restrictions') }}
                                                </h6>

                                                <!-- Fallback Behavior -->
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold mb-2">{{ __('If Criteria Not Met') }}</label>
                                                    <div class="d-flex flex-column gap-2 ms-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="fallback_action" value="level1" id="fallback_level1" {{ $settings['fallback_action'] == 'level1' ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="fallback_level1">
                                                                {{ __('Route to Level 1 Approver') }}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="fallback_action" value="department_head" id="fallback_dept" {{ $settings['fallback_action'] == 'department_head' ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="fallback_dept">
                                                                {{ __('Route to Department Head') }}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="fallback_action" value="reject" id="fallback_reject" {{ $settings['fallback_action'] == 'reject' ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="fallback_reject">
                                                                {{ __('Reject automatically') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Notification Settings -->
                                                <div class="mb-3 pt-3 border-top">
                                                    <label class="form-label small fw-bold mb-2">{{ __('Notifications') }}</label>
                                                    <div class="d-flex flex-column gap-2 ms-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="notify_supervisor" value="1" id="notify_supervisor" {{ $settings['notify_supervisor'] ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="notify_supervisor">
                                                                {{ __('Notify supervisor of auto-approved requests') }}
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="notify_employee" value="1" id="notify_employee" {{ $settings['notify_employee'] ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="notify_employee">
                                                                {{ __('Notify employee immediately') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Department Restrictions -->
                                                <div class="mb-0 pt-3 border-top">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="restrict_to_departments" value="1" id="restrict_departments" {{ $settings['restrict_to_departments'] ? 'checked' : '' }} onchange="toggleDepartmentSelect(this)">
                                                        <label class="form-check-label small fw-bold" for="restrict_departments">
                                                            {{ __('Restrict to specific departments') }}
                                                        </label>
                                                    </div>
                                                    <select class="form-select form-select-sm {{ $settings['restrict_to_departments'] ? '' : 'd-none' }} ms-3" name="allowed_departments[]" id="department_select" multiple size="4">
                                                        <option value="it" {{ in_array('it', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('IT Department') }}</option>
                                                        <option value="operations" {{ in_array('operations', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('Operations') }}</option>
                                                        <option value="customer_service" {{ in_array('customer_service', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('Customer Service') }}</option>
                                                        <option value="sales" {{ in_array('sales', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('Sales') }}</option>
                                                        <option value="finance" {{ in_array('finance', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('Finance') }}</option>
                                                        <option value="hr" {{ in_array('hr', $settings['allowed_departments']) ? 'selected' : '' }}>{{ __('HR') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer bg-white p-4 border-top-0">
                            <div class="alert alert-secondary border-0 mb-0 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="la la-shield-alt fs-3"></i>
                                    <span class="small fw-bold">{{ __('Configuration changes apply to new requests only.') }}</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" onclick="location.reload()">{{ __('Reset') }}</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-4">{{ __('Save Changes') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('page-styles')
<style>
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
    .bg-info-light { background-color: rgba(23, 162, 184, 0.1); }
    .bg-primary-light { background-color: rgba(0, 123, 255, 0.1); }
    .border-primary-light { border-color: rgba(0, 123, 255, 0.2) !important; }
    .bg-light-subtle { background-color: #fbfbfc; }
    .extra-small { font-size: 0.7rem; }
    .text-info-emphasis { color: #055160; }
</style>
@endpush

@push('page-scripts')
<script>
    function toggleDepartmentSelect(checkbox) {
        const departmentSelect = document.getElementById('department_select');
        
        if (checkbox.checked) {
            departmentSelect.classList.remove('d-none');
        } else {
            departmentSelect.classList.add('d-none');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialization if needed
    });
</script>
@endpush

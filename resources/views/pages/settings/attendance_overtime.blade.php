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
                <li class="breadcrumb-item active">{{ __('Overtime Approval') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <form action="{{ route('admin.attendance-settings.overtime-approval.update') }}" method="POST" id="overtime-approval-form">
            @csrf
            @method('POST')
            
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                <i class="la la-check-circle text-primary" style="font-size: 1.5rem;"></i>
                                {{ __('Overtime Approval Configuration') }}
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
                        
                        <!-- 1. Feature Toggle & Warning -->
                        <div class="p-3 bg-light rounded border border-primary-light d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-primary mb-1">{{ __('Policy Enforcement') }}</h6>
                                <p class="text-muted small mb-0">{{ __('When enabled, overtime requires manager/HR validation.') }}</p>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge rounded-pill bg-success-light text-success px-3 py-2">
                                    <i class="la la-check-circle fs-6 me-1"></i> {{ __('Active (Approval Required)') }}
                                </span>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" role="switch" name="overtime_approval_enabled" value="1" {{ $settings['overtime_approval_enabled'] ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning border-0 bg-warning-light mt-3 mb-0 d-flex align-items-center gap-2">
                            <i class="la la-exclamation-triangle text-warning fs-4"></i>
                            <div class="small fw-semibold text-warning-emphasis">
                                {{ __('Note: Approval workflow is not yet enforced at the payroll calculation level.') }}
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            
                            <!-- CARD 1: Core Configuration (Source, Timing) -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none bg-light-subtle">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                            <i class="la la-cog me-1"></i> {{ __('Core Request Settings') }}
                                        </h6>
                                        
                                        <!-- Source -->
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold">{{ __('Request Initiation') }}</label>
                                            <div class="d-flex flex-column gap-2 ms-2">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" name="request_by_employee" value="1" {{ $settings['request_by_employee'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Employee') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" name="request_by_supervisor" value="1" {{ $settings['request_by_supervisor'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Direct Supervisor') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" name="request_by_hr" value="1" {{ $settings['request_by_hr'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('HR Administrator') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" name="request_auto_generated" value="1" {{ $settings['request_auto_generated'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Auto-generated (Attendance)') }}</label></div>
                                            </div>
                                        </div>

                                        <!-- Timing -->
                                        <div class="mb-0">
                                            <label class="form-label small fw-bold">{{ __('Request Timing') }}</label>
                                            <div class="d-flex flex-wrap gap-3 ms-2">
                                                <div class="form-check"><input class="form-check-input" type="radio" name="request_timing" value="pre" {{ $settings['request_timing'] == 'pre' ? 'checked' : '' }}><label class="form-check-label small">{{ __('Pre-approval') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="radio" name="request_timing" value="post" {{ $settings['request_timing'] == 'post' ? 'checked' : '' }}><label class="form-check-label small">{{ __('Post-approval') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="radio" name="request_timing" value="both" {{ $settings['request_timing'] == 'both' ? 'checked' : '' }}><label class="form-check-label small">{{ __('Both') }}</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 2: Workflow Setup -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none bg-light-subtle">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                            <i class="la la-project-diagram me-1"></i> {{ __('Approval Workflow') }}
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">{{ __('Hierarchy Levels') }}</label>
                                            <select class="form-select form-select-sm" name="approval_hierarchy" id="approval_hierarchy" style="max-width: 120px;" onchange="handleHierarchyChange(this.value)">
                                                <option value="1" {{ $settings['approval_hierarchy'] == 1 ? 'selected' : '' }}>1 Level</option>
                                                <option value="2" {{ $settings['approval_hierarchy'] == 2 ? 'selected' : '' }}>2 Levels</option>
                                                <option value="3" {{ $settings['approval_hierarchy'] == 3 ? 'selected' : '' }}>3 Levels</option>
                                            </select>
                                        </div>

                                        <div class="p-3 bg-white border rounded mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small fw-bold text-primary">{{ __('LEVEL 1') }}</span>
                                                <span class="badge bg-primary-light text-primary extra-small">{{ __('Mandatory') }}</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <label class="form-label extra-small text-muted mb-0">{{ __('Approver Role') }}</label>
                                                    <select class="form-select form-select-sm" name="level1_approver">
                                                        <option value="supervisor" {{ $settings['level1_approver'] == 'supervisor' ? 'selected' : '' }}>{{ __('Direct Supervisor') }}</option>
                                                        <option value="department_head" {{ $settings['level1_approver'] == 'department_head' ? 'selected' : '' }}>{{ __('Department Head') }}</option>
                                                        <option value="hr" {{ $settings['level1_approver'] == 'hr' ? 'selected' : '' }}>{{ __('HR Administrator') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Interactive Level 2 Container -->
                                        <div id="level_2_container" class="mt-3">
                                            <!-- Add Level 2 Button -->
                                            <div id="btn_add_level_2" class="p-3 border border-dashed rounded text-center" 
                                                 style="cursor: pointer; transition: all 0.2s;"
                                                 onmouseover="this.classList.add('bg-light')"
                                                 onmouseout="this.classList.remove('bg-light')"
                                                 onclick="addLevel('2')">
                                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 border-0 pointer-events-none">
                                                    <i class="la la-plus"></i> {{ __('Add Level 2') }}
                                                </button>
                                            </div>

                                            <!-- Level 2 Form (Hidden by default) -->
                                            <div id="level_2_form" class="d-none animation-fade-in">
                                                <div class="p-3 bg-white border rounded mb-3">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="small fw-bold text-secondary">{{ __('LEVEL 2') }}</span>
                                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.75rem;"
                                                                onclick="addLevel('1')">
                                                            <i class="la la-trash-alt me-1"></i>{{ __('Remove') }}
                                                        </button>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                    <label class="form-label extra-small text-muted mb-0">{{ __('Approver Role') }}</label>
                                                    <select class="form-select form-select-sm" name="level2_approver">
                                                        <option value="department_head" {{ $settings['level2_approver'] == 'department_head' ? 'selected' : '' }}>{{ __('Department Head') }}</option>
                                                        <option value="hr" {{ $settings['level2_approver'] == 'hr' ? 'selected' : '' }}>{{ __('HR Administrator') }}</option>
                                                        <option value="director" {{ $settings['level2_approver'] == 'director' ? 'selected' : '' }}>{{ __('Director') }}</option>
                                                    </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Add Level 3 Button (Visible when Level 2 is active) -->
                                                <div id="btn_add_level_3" class="p-3 border border-dashed rounded text-center d-none" 
                                                     style="cursor: pointer; transition: all 0.2s;"
                                                     onmouseover="this.classList.add('bg-light')"
                                                     onmouseout="this.classList.remove('bg-light')"
                                                     onclick="addLevel('3')">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 border-0 pointer-events-none">
                                                        <i class="la la-plus"></i> {{ __('Add Level 3') }}
                                                    </button>
                                                </div>

                                                <!-- Level 3 Form (Hidden by default) -->
                                                <div id="level_3_form" class="p-3 bg-white border rounded d-none animation-fade-in">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="small fw-bold text-secondary">{{ __('LEVEL 3') }}</span>
                                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.75rem;"
                                                                onclick="addLevel('2')">
                                                            <i class="la la-trash-alt me-1"></i>{{ __('Remove') }}
                                                        </button>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-12">
                                                            <label class="form-label extra-small text-muted mb-0">{{ __('Approver Role') }}</label>
                                                            <select class="form-select form-select-sm" name="level3_approver">
                                                                <option value="director" {{ $settings['level3_approver'] == 'director' ? 'selected' : '' }}>{{ __('Director') }}</option>
                                                                <option value="managing_director" {{ $settings['level3_approver'] == 'managing_director' ? 'selected' : '' }}>{{ __('Managing Director') }}</option>
                                                                <option value="board" {{ $settings['level3_approver'] == 'board' ? 'selected' : '' }}>{{ __('Board') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-2 border-top">
                                            <label class="form-label small fw-bold">{{ __('Approval Mode') }}</label>
                                            <div class="d-flex gap-4 ms-2 mt-1">
                                                <div class="form-check"><input class="form-check-input" type="radio" name="approval_mode" value="sequential" {{ $settings['approval_mode'] == 'sequential' ? 'checked' : '' }}><label class="form-check-label small">{{ __('Sequential') }}</label></div>
                                                <div class="form-check"><input class="form-check-input" type="radio" name="approval_mode" value="parallel" {{ $settings['approval_mode'] == 'parallel' ? 'checked' : '' }}><label class="form-check-label small">{{ __('Parallel') }}</label></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 3: Rules & Validation -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none bg-light-subtle">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                            <i class="la la-balance-scale me-1"></i> {{ __('Rules & Validation') }}
                                        </h6>
                                        
                                        <div class="mb-0">
                                            <label class="form-label small fw-bold mb-1">{{ __('Attendance Dependency') }}</label>
                                            <div class="form-check ms-2"><input class="form-check-input" type="checkbox" name="require_punch_records" value="1" {{ $settings['require_punch_records'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Require valid punch records') }}</label></div>
                                            <div class="form-check ms-2"><input class="form-check-input" type="checkbox" name="allow_manual_ot" value="1" {{ $settings['allow_manual_ot'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Allow purely manual OT') }}</label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 4: Interaction, Notifications & Logs -->
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-none bg-light-subtle">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="fw-bold mb-3 text-uppercase small text-muted border-bottom pb-2">
                                            <i class="la la-exchange-alt me-1"></i> {{ __('Interaction & Auditing') }}
                                        </h6>
                                        
                                        <!-- Payroll Sync -->
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold mb-2">{{ __('Payroll Integration') }}</label>
                                            
                                            <!-- Toggle for Enable/Disable -->
                                            <div class="d-flex align-items-center justify-content-between p-2 bg-white border rounded">
                                                <span class="small fw-bold text-dark">{{ __('Enable Auto-Sync to Payroll') }}</span>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="enable_payroll_sync" id="enable_payroll_sync" value="1" {{ $settings['enable_payroll_sync'] ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            
                                            <p class="extra-small text-info mt-2 mb-0 lh-sm">
                                                <i class="la la-info-circle"></i> {{ __('Automatic syncing is currently under development.') }}
                                            </p>
                                        </div>

                                        <!-- Notifications -->
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold mb-1">{{ __('Send Notifications To') }}</label>
                                            <div class="row g-2 ms-1">
                                                <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="notify_approver" value="1" {{ $settings['notify_approver'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Approver') }}</label></div></div>
                                                <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="notify_employee" value="1" {{ $settings['notify_employee'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('Employee') }}</label></div></div>
                                                <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="notify_hr" value="1" {{ $settings['notify_hr'] ? 'checked' : '' }}><label class="form-check-label small">{{ __('HR Team') }}</label></div></div>
                                            </div>
                                        </div>

                                        <!-- Audit -->
                                        <div class="mt-auto p-3 bg-dark-light border rounded">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="la la-history text-muted"></i>
                                                    <span class="small fw-bold text-muted">{{ __('Audit Logging') }}</span>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="enable_audit_logging" id="enable_audit_logging" value="1" {{ $settings['enable_audit_logging'] ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                            <p class="extra-small text-muted mb-0">
                                                {{ __('When enabled, creation, approval, and rejection events are automatically logged.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- /row -->
                    </div> <!-- /card-body -->

                    <div class="card-footer bg-white p-4 border-top-0">
                        <div class="alert alert-secondary border-0 mb-0 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="la la-shield-alt fs-3"></i>
                                <span class="small fw-bold">{{ __('Safety Memo: configuration changes do not affect past payroll batches.') }}</span>
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

@push('page-scripts')
<script>
    function handleHierarchyChange(level) {
        const level2Form = document.getElementById('level_2_form');
        const level3Form = document.getElementById('level_3_form');
        const btnAddLevel2 = document.getElementById('btn_add_level_2');
        const btnAddLevel3 = document.getElementById('btn_add_level_3');
        
        // Reset visibility
        level2Form.classList.add('d-none');
        level3Form.classList.add('d-none');
        btnAddLevel2.classList.add('d-none');
        btnAddLevel3.classList.add('d-none');
        
        if (level == '1') {
            btnAddLevel2.classList.remove('d-none');
        } else if (level == '2') {
            level2Form.classList.remove('d-none');
            btnAddLevel3.classList.remove('d-none');
        } else if (level == '3') {
            level2Form.classList.remove('d-none');
            level3Form.classList.remove('d-none');
        }
    }

    // Initialize UI on page load
    document.addEventListener('DOMContentLoaded', function() {
        const hierarchySelect = document.getElementById('approval_hierarchy');
        if (hierarchySelect) {
            handleHierarchyChange(hierarchySelect.value);
        }
    });

    // Handle "Add Level X" buttons
    function addLevel(level) {
        const select = document.getElementById('approval_hierarchy');
        select.value = level;
        handleHierarchyChange(level);
    }
</script>
@endpush

@push('page-styles')
<style>
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-light { background-color: rgba(23, 162, 184, 0.1); }
    .bg-primary-light { background-color: rgba(0, 123, 255, 0.1); }
    .bg-primary-light-subtle { background-color: rgba(0, 123, 255, 0.05); }
    .border-primary-light { border-color: rgba(0, 123, 255, 0.2) !important; }
    .bg-light-subtle { background-color: #fbfbfc; }
    .bg-dark-light { background-color: #f4f6f8; }
    .extra-small { font-size: 0.7rem; }
    .text-info-emphasis { color: #055160; }
</style>
@endpush

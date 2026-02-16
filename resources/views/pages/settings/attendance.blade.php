@extends('pages.settings.index')

@section('page-header-section')
    <!-- Page Header -->
    <x-breadcrumb>
        <x-slot name="title">{{ __('Attendance Settings') }}</x-slot>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <form action="{{ route('admin.attendance-settings.clear-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="{{ __('Clear Cache') }}">
                        <i class="la la-refresh"></i>
                    </button>
                </form>
                <form action="{{ route('admin.attendance-settings.reset') }}" method="POST" class="d-inline" 
                      onsubmit="return confirm('{{ __('Are you sure you want to reset all settings to defaults?') }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('Reset to Defaults') }}">
                        <i class="la la-undo"></i>
                    </button>
                </form>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" form="attendance-settings-form" class="btn btn-primary btn-sm">
                        <i class="la la-save"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </x-slot>
    </x-breadcrumb>
@endsection

@section('page-section')
    @php
        // Helper to safely get value from settings array
        $getValue = function($category, $key, $default = null) use ($settings) {
            $catSettings = $settings[$category] ?? [];
            $setting = collect($catSettings)->firstWhere('key', $key);
            return $setting['value'] ?? $default;
        };
        
        // --- Capture Methods ---
        $singleMethodOnly = $getValue('capture_methods', 'single_method_only', false);
        $allowedMethods = $getValue('capture_methods', 'allowed_methods', []);
        $selfieEnabled = $getValue('capture_methods', 'selfie_verification_enabled', false);
        
        // --- Working Days ---
        $workingDays = $getValue('working_days', 'working_days', []);
        
        // --- Time Rules ---
        $workDayStartTime = $getValue('time_rules', 'work_day_start_time', '09:00');
        $workDayEndTime = $getValue('time_rules', 'work_day_end_time', '17:00');
        $gracePeriod = $getValue('time_rules', 'grace_period_minutes', 0);
        $minWorkHours = $getValue('time_rules', 'minimum_work_hours', 8);
        $autoClockOut = $getValue('time_rules', 'auto_clockout_enabled', true);
        $autoClockOutTime = $getValue('time_rules', 'auto_clockout_time', '23:59');


        // --- Penalties ---
        $latePenalty = $getValue('penalties', 'late_arrival_penalty_enabled', false);
        $earlyPenalty = $getValue('penalties', 'early_departure_penalty_enabled', false);


        // --- Shifts ---
        $shiftsEnabled = $getValue('shifts', 'shifts_enabled', true);
        $shiftMode = $getValue('shifts', 'shift_mode', 'mandatory');
        $graceInEnabled = $getValue('shifts', 'grace_in_enabled', true);
        $graceInMinutes = $getValue('shifts', 'grace_in_minutes', 10);
        $graceOutEnabled = $getValue('shifts', 'grace_out_enabled', true);
        $graceOutMinutes = $getValue('shifts', 'grace_out_minutes', 10);
        $nightShift = $getValue('shifts', 'night_shift_enabled', true);
        $rotationalShift = $getValue('shifts', 'rotational_shift_enabled', false);
        $flexibleHours = $getValue('shifts', 'flexible_hours_enabled', false);
        $splitShift = $getValue('shifts', 'split_shift_enabled', false);

        // --- Policies ---
        $lateArrival = $getValue('policies', 'late_arrival_enabled', true);
        $earlyCheckout = $getValue('policies', 'early_checkout_enabled', true);
        $halfDay = $getValue('policies', 'half_day_enabled', true);
        $absence = $getValue('policies', 'absence_enabled', true);
        $overtime = $getValue('policies', 'overtime_enabled', true);
        $wfh = $getValue('policies', 'wfh_enabled', false);
        $compOff = $getValue('policies', 'comp_off_enabled', true);
        $missingPunch = $getValue('policies', 'missing_punch_enabled', true);

        // --- Late Arrival Config (Direct Access) ---
        $lateArrivalGrace = \App\Models\AttendanceSetting::get('late_arrival_grace_period', 15);
        $lateArrivalPenalty = \App\Models\AttendanceSetting::get('late_arrival_penalty_type', 'none');
        $lateArrivalDeductionAmount = \App\Models\AttendanceSetting::get('late_arrival_deduction_amount', 0);
        $lateArrivalDeductionType = \App\Models\AttendanceSetting::get('late_arrival_deduction_type', 'fixed');

        // --- Early Checkout Config (Direct Access) ---
        $earlyCheckoutGrace = \App\Models\AttendanceSetting::get('early_checkout_grace_period', 15);
        $earlyCheckoutPenalty = \App\Models\AttendanceSetting::get('early_checkout_penalty_type', 'none');
        $earlyCheckoutDeductionAmount = \App\Models\AttendanceSetting::get('early_checkout_deduction_amount', 0);
        $earlyCheckoutDeductionType = \App\Models\AttendanceSetting::get('early_checkout_deduction_type', 'fixed');

        // --- Overtime Config (Direct Access) ---
        $overtimeMinMinutes = \App\Models\AttendanceSetting::get('overtime_min_minutes', 60);
        $overtimeRateNormal = \App\Models\AttendanceSetting::get('overtime_rate_normal', 1.25);
        $overtimeRateNight = \App\Models\AttendanceSetting::get('overtime_rate_night', 1.5);
        $overtimeRateDayOff = \App\Models\AttendanceSetting::get('overtime_rate_dayoff', 2.0);
        $overtimeRateHoliday = \App\Models\AttendanceSetting::get('overtime_rate_holiday', 2.5);

        // --- Web Portal Config (Direct Access) ---
        $webPortalRequireGPS = \App\Models\AttendanceSetting::get('web_portal_require_gps', false);
        $webPortalIPWhitelist = \App\Models\AttendanceSetting::get('web_portal_ip_whitelist', '');
        $webPortalAllowedHoursStart = \App\Models\AttendanceSetting::get('web_portal_allowed_hours_start', '');
        $webPortalAllowedHoursEnd = \App\Models\AttendanceSetting::get('web_portal_allowed_hours_end', '');

        // --- Manual Entry Config ---
        $manualEntryPermissionMode = \App\Models\AttendanceSetting::get('manual_entry_permission_mode', 'roles');
        $manualEntryAllowedRoles = \App\Models\AttendanceSetting::get('manual_entry_allowed_roles', []);
        
        $manualEntryApprovalPolicy = \App\Models\AttendanceSetting::get('manual_entry_approval_policy', 'auto_approve');
        $manualEntryApprovalStructure = \App\Models\AttendanceSetting::get('manual_entry_approval_structure', 'single');
        $manualEntryApproverEntity = \App\Models\AttendanceSetting::get('manual_entry_approver_entity', 'role');
        $manualEntryApproverRoleId = \App\Models\AttendanceSetting::get('manual_entry_approver_role_id', '');
        $manualEntryApproverUserId = \App\Models\AttendanceSetting::get('manual_entry_approver_user_id', '');
        
        $manualEntryHierarchicalRoleIds = \App\Models\AttendanceSetting::get('manual_entry_hierarchical_role_ids', []);
        $manualEntryHierarchicalUserIds = \App\Models\AttendanceSetting::get('manual_entry_hierarchical_user_ids', []);

        $manualEntryTrackProject = \App\Models\AttendanceSetting::get('manual_entry_track_project', false);
        $manualEntryRequireProject = \App\Models\AttendanceSetting::get('manual_entry_require_project', false);
        $manualEntryRequireReason = \App\Models\AttendanceSetting::get('manual_entry_require_reason', true);
        $manualEntryMaxDaysBack = \App\Models\AttendanceSetting::get('manual_entry_max_days_back', 30);
        $manualEntryAllowFuture = \App\Models\AttendanceSetting::get('manual_entry_allow_future', false);

        // --- Approvals ---
        $missedPunchApproval = $getValue('approvals', 'missed_punch_approval_enabled', true);
        $missedPunchRetroactiveLimit = \App\Models\AttendanceSetting::get('missed_punch_retroactive_limit', 7);
        $missedPunchMaxRequests = \App\Models\AttendanceSetting::get('missed_punch_max_requests_per_month', 5);
        $missedPunchRequireReason = \App\Models\AttendanceSetting::get('missed_punch_require_reason', true);
        $missedPunchApprovalMode = \App\Models\AttendanceSetting::get('missed_punch_approval_mode', 'manager');

        $correctionRetroactiveLimit = \App\Models\AttendanceSetting::get('correction_retroactive_limit', 30);
        $correctionRequireReason = \App\Models\AttendanceSetting::get('correction_require_reason', true);
        $correctionAuditTrail = \App\Models\AttendanceSetting::get('correction_audit_trail_enabled', true);

        $correctionApproval = $getValue('approvals', 'correction_approval_enabled', true);
        $overtimeApproval = $getValue('approvals', 'overtime_approval_enabled', true);
        $autoApproval = $getValue('approvals', 'auto_approval_enabled', false);

        // --- Integrations ---
        $leaveIntegration = $getValue('integrations', 'leave_integration_enabled', true);
        $holidayIntegration = $getValue('integrations', 'holiday_integration_enabled', true);
        $payrollLock = $getValue('integrations', 'payroll_lock_enabled', true);
        $payrollExport = $getValue('integrations', 'payroll_export_enabled', true);

        // --- Audit & Security ---
        $auditLogging = $getValue('audit_security', 'audit_logging_enabled', true);
        $auditLevel = $getValue('audit_security', 'audit_level', 'standard');
        $tamperDetection = $getValue('audit_security', 'tamper_detection_enabled', true);
        $complianceMode = $getValue('audit_security', 'compliance_mode', false);


        // Weekdays for selection
        $workingDaysList = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $allAndWeekendDays = [
            'monday' => __('Monday'), 
            'tuesday' => __('Tuesday'), 
            'wednesday' => __('Wednesday'), 
            'thursday' => __('Thursday'), 
            'friday' => __('Friday'), 
            'saturday' => __('Saturday'), 
            'sunday' => __('Sunday')
        ];
    @endphp

    <form action="{{ route('admin.attendance-settings.update') }}" method="POST" id="attendance-settings-form">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Capture Methods -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-fingerprint" title="{{ __('Capture Methods') }}" description="{{ __('How employees mark attendance') }}" />

                    <x-settings.row label="{{ __('Biometric Devices') }}" description="{{ __('Fingerprint / Face ID') }}" 
                                    id="biometric" configureLink="/config/biometric" :showConfigure="in_array('biometric', $allowedMethods)">
                        <div class="form-check form-switch">
                            <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="biometric" 
                                   {{ in_array('biometric', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('biometric', this)">
                        </div>
                    </x-settings.row>
                    
                    <x-settings.row label="{{ __('Mobile App') }}" description="{{ __('GPS / Selfie check-in') }}"
                                    id="mobile" configureLink="/config/mobile" :showConfigure="in_array('mobile', $allowedMethods)">
                        <div class="form-check form-switch">
                            <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="mobile" 
                                   {{ in_array('mobile', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('mobile', this)">
                        </div>
                    </x-settings.row>
                    
                    <x-settings.row label="{{ __('Web Portal') }}" description="{{ __('Browser-based check-in') }}"
                                    id="web_portal" configureLink="#" :showConfigure="in_array('web_based', $allowedMethods)">
                        <div class="form-check form-switch">
                             <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="web_based" 
                                   {{ in_array('web_based', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('web_portal', this)">
                        </div>
                    </x-settings.row>

                     <x-settings.row label="{{ __('Manual Entry') }}" description="{{ __('HR/Admin manual entry') }}"
                                     id="manual" configureLink="#" :showConfigure="in_array('manual', $allowedMethods)"
                                     data-configure-id="configure-manual-entry-link">
                        <div class="form-check form-switch">
                            <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="manual"
                                   {{ in_array('manual', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('manual', this)">
                        </div>
                    </x-settings.row>

                     <x-settings.row label="{{ __('Single Method Only') }}" description="{{ __('Restrict to one method only') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="single_method_only" name="single_method_only" value="true" 
                                   {{ $singleMethodOnly ? 'checked' : '' }} onchange="toggleConfigLink('single_method_only', this); toggleMethodSelectionMode();">
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Approvals and Workflow -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-check-square" title="{{ __('Approvals and Workflow') }}" description="{{ __('Request and approval settings') }}" />

                    <x-settings.row label="{{ __('Missed Punch Requests') }}" description="{{ __('Allow employees to request corrections') }}"
                                    id="missed_punch" configureLink="#" :showConfigure="$missedPunchApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missed_punch_approval_enabled" value="true" {{ $missedPunchApproval ? 'checked' : '' }} onchange="toggleConfigLink('missed_punch', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Attendance Corrections') }}" description="{{ __('HR/Manager can modify attendance') }}"
                                    id="corrections" :configureLink="'#'" :showConfigure="$correctionApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="correction_approval_enabled" value="true" {{ $correctionApproval ? 'checked' : '' }} onchange="toggleConfigLink('corrections', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Overtime Approval') }}" description="{{ __('Require approval for overtime') }}"
                                    id="overtime_approval" configureLink="#" :showConfigure="$overtimeApproval">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="overtime_approval_enabled" value="true" {{ $overtimeApproval ? 'checked' : '' }} onchange="toggleConfigLink('overtime_approval', this)">
                            </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Auto-Approval') }}" 
                                    id="auto_approval" configureLink="#" :showConfigure="$autoApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="auto_approval_enabled" value="true" {{ $autoApproval ? 'checked' : '' }} onchange="toggleConfigLink('auto_approval', this)">
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Shifts and Schedule -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-clock" title="{{ __('Shifts and Schedule') }}" description="{{ __('Work timing configuration') }}" />
                    
                    <x-settings.row label="{{ __('Enable Shifts') }}" description="{{ __('Use shift-based attendance') }}"
                                    id="shifts_enabled" configureLink="{{ route('shifts.index') }}" :showConfigure="$shiftsEnabled">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="shifts_enabled" value="true" 
                                   {{ $shiftsEnabled ? 'checked' : '' }} onchange="toggleShifts(this); toggleConfigLink('shifts_enabled', this)">
                        </div>
                    </x-settings.row>

                    <div id="shift_config" class="{{ $shiftsEnabled ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Shift Assignment') }}" indent>
                            <select name="shift_mode" class="form-select form-select-sm" style="width: 130px;">
                                <option value="mandatory" {{ $shiftMode === 'mandatory' ? 'selected' : '' }}>{{ __('Mandatory') }}</option>
                                <option value="optional" {{ $shiftMode === 'optional' ? 'selected' : '' }}>{{ __('Optional') }}</option>
                                <option value="disabled" {{ $shiftMode === 'disabled' ? 'selected' : '' }}>{{ __('Disabled') }}</option>
                            </select>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Grace Period (In)') }}" description="{{ __('Buffer before marking late') }}" indent>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="grace_in_enabled" value="true" 
                                           {{ $graceInEnabled ? 'checked' : '' }} onchange="toggleGraceIn(this)">
                                </div>
                                <div class="input-group input-group-sm" style="width: 100px;" id="grace_in_input">
                                    <input type="number" name="grace_in_minutes" class="form-control {{ $graceInEnabled ? '' : 'text-muted' }}" 
                                           value="{{ $graceInMinutes }}" min="0" max="60" 
                                           {{ $graceInEnabled ? '' : 'readonly style=background-color:#e9ecef;' }}>
                                    <span class="input-group-text">min</span>
                                </div>
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Grace Period (Out)') }}" description="{{ __('Buffer before early leave') }}" indent>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="grace_out_enabled" value="true" 
                                           {{ $graceOutEnabled ? 'checked' : '' }} onchange="toggleGraceOut(this)">
                                </div>
                                <div class="input-group input-group-sm" style="width: 100px;" id="grace_out_input">
                                    <input type="number" name="grace_out_minutes" class="form-control {{ $graceOutEnabled ? '' : 'text-muted' }}" 
                                           value="{{ $graceOutMinutes }}" min="0" max="60" 
                                           {{ $graceOutEnabled ? '' : 'readonly style=background-color:#e9ecef;' }}>
                                    <span class="input-group-text">min</span>
                                </div>
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Night Shift') }}" description="{{ __('Shifts crossing midnight') }}" indent
                                        id="night_shift" configureLink="{{ route('admin.attendance-settings.night-shift') }}" :showConfigure="!$nightShift">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="night_shift_enabled" value="true" {{ $nightShift ? 'checked' : '' }} onchange="toggleConfigLink('night_shift', this, true)">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Rotational Shifts') }}" description="{{ __('Auto-rotate employee shifts') }}" indent
                                        id="rotational_shift" configureLink="{{ route('shifts.rotation.index') }}" :showConfigure="$rotationalShift">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="rotational_shift_enabled" value="true" {{ $rotationalShift ? 'checked' : '' }} onchange="toggleConfigLink('rotational_shift', this)">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Flexible Hours') }}" description="{{ __('Employees choose work timing') }}" indent
                                        id="flexible_hours" :configureLink="route('admin.attendance-settings.flexible')" :showConfigure="$flexibleHours">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="flexible_hours_enabled" value="true" {{ $flexibleHours ? 'checked' : '' }} onchange="toggleConfigLink('flexible_hours', this)">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Split Shifts') }}" description="{{ __('Shifts with breaks') }}" indent>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="split_shift_enabled" value="true" {{ $splitShift ? 'checked' : '' }}>
                            </div>
                        </x-settings.row>
                    </div>
                    </div>
                </x-settings.section>
            </div>

            <!-- Attendance Policies -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-file-text" title="{{ __('Attendance Policies') }}" description="{{ __('Rules for attendance calculation') }}" />

                    <x-settings.row label="{{ __('Late Arrival Rules') }}" description="{{ __('Penalties for coming late') }}"
                                    id="late_arrival" configureLink="#" :showConfigure="$lateArrival">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="late_arrival_enabled" value="true" {{ $lateArrival ? 'checked' : '' }} onchange="toggleConfigLink('late_arrival', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Early Checkout Rules') }}" description="{{ __('Penalties for leaving early') }}"
                                    id="early_checkout" configureLink="#" :showConfigure="$earlyCheckout">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="early_checkout_enabled" value="true" {{ $earlyCheckout ? 'checked' : '' }} onchange="toggleConfigLink('early_checkout', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Overtime') }}" description="{{ __('Track and compensate extra hours') }}"
                                    id="overtime" configureLink="#" :showConfigure="$overtime">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="overtime_enabled" value="true" {{ $overtime ? 'checked' : '' }} onchange="toggleConfigLink('overtime', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Half Day') }}" description="{{ __('Allow half-day attendance') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="half_day_enabled" value="true" {{ $halfDay ? 'checked' : '' }}>
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Absence Tracking') }}" description="{{ __('Track employee absences') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="absence_enabled" value="true" {{ $absence ? 'checked' : '' }}>
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Work from Home') }}" description="{{ __('Remote attendance rules') }}"
                                    id="wfh" configureLink="/policies/wfh" :showConfigure="$wfh">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="wfh_enabled" value="true" {{ $wfh ? 'checked' : '' }} onchange="toggleConfigLink('wfh', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Compensatory Off') }}" description="{{ __('Earn leave for extra work') }}"
                                    id="comp_off" configureLink="/policies/comp-off" :showConfigure="$compOff">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="comp_off_enabled" value="true" {{ $compOff ? 'checked' : '' }} onchange="toggleConfigLink('comp_off', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Missing Clock-In/Out') }}" description="{{ __('Handle incomplete attendance records') }}"
                                    id="missing_punch" configureLink="#" :showConfigure="$missingPunch">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missing_punch_enabled" value="true" {{ $missingPunch ? 'checked' : '' }} onchange="toggleConfigLink('missing_punch', this)">
                        </div>
                         <!-- Custom Link Override via JS -->
                         @push('page-scripts')
                         <script>
                             document.addEventListener('DOMContentLoaded', function() {
                                 const link = document.getElementById('config_link_missing_punch');
                                 if(link) {
                                      link.removeAttribute('href');
                                      link.style.cursor = 'pointer';
                                      link.setAttribute('data-bs-toggle', 'modal');
                                      link.setAttribute('data-bs-target', '#missingPunchModal');
                                 }
                             });
                         </script>
                         @endpush
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Time Rules -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-clock-o" title="{{ __('Time Rules') }}" description="{{ __('Grace periods and limits') }}" />
                    
                    <x-settings.row label="{{ __('Grace Period') }}" description="{{ __('Allowed delay before late mark') }}">
                         <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" name="grace_period_minutes" class="form-control" value="{{ $gracePeriod }}" min="0" max="60">
                            <span class="input-group-text">{{ __('Min') }}</span>
                        </div>
                    </x-settings.row>
                    
                     <x-settings.row label="{{ __('Auto Clock-Out') }}" description="{{ __('Automatically check out at fixed time') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="auto_clockout_enabled" value="true" 
                                   {{ $autoClockOut ? 'checked' : '' }} onchange="toggleAutoClockOut(this); toggleConfigLink('auto_clockout', this)">
                        </div>
                    </x-settings.row>
                    
                    <div id="auto_clockout_config" class="{{ $autoClockOut ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Clock-Out Time') }}" indent>
                            <input type="time" name="auto_clockout_time" class="form-control form-control-sm" value="{{ $autoClockOutTime }}" style="width: 140px;">
                        </x-settings.row>
                    </div>
                    </div>
                </x-settings.section>
            </div>



            <!-- General Schedule -->
            <div class="col-md-6">
                <x-settings.section class="settings-section">
                    <x-settings.header icon="la la-calendar" title="{{ __('General Schedule') }}" description="{{ __('Working days and standard hours') }}" />
                    
                    <x-settings.row label="{{ __('Working Days') }}" description="{{ __('Select working days') }}">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($workingDaysList as $day)
                                <input type="checkbox" class="btn-check working-day-input" 
                                       name="working_days[]" id="day_{{ $day }}" value="{{ $day }}" 
                                       {{ in_array($day, $workingDays) ? 'checked' : '' }} autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm pb-0 pt-0 d-flex align-items-center justify-content-center" 
                                       for="day_{{ $day }}" style="width: 36px; height: 32px; font-weight: 600;">
                                    {{ substr($allAndWeekendDays[$day], 0, 1) }}
                                </label>
                            @endforeach
                        </div>
                    </x-settings.row>
                    
                    
                    <x-settings.row label="{{ __('Work Day Duration') }}" description="{{ __('Official start and end time') }}">
                        <div class="d-flex align-items-center gap-2">
                            <input type="time" name="work_day_start_time" class="form-control form-control-sm" value="{{ $workDayStartTime }}" style="width: 140px;">
                            <span class="text-muted">-</span>
                            <input type="time" name="work_day_end_time" class="form-control form-control-sm" value="{{ $workDayEndTime }}" style="width: 140px;">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Minimum Hours') }}" description="{{ __('Required hours for full day') }}">
                         <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" id="minimum_work_hours" name="minimum_work_hours" class="form-control" value="{{ $minWorkHours }}" step="0.5" min="0" max="24">
                            <span class="input-group-text">{{ __('Hrs') }}</span>
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Penalties -->
            <!-- <div class="col-md-6">
                <x-settings.section class="settings-section">
                    <x-settings.header icon="la la-exclamation-triangle" title="{{ __('Penalties') }}" description="{{ __('Policy violations') }}" />
                    
                    <x-settings.row label="{{ __('Late Arrival') }}" description="{{ __('Penalty for late clock-in') }}"
                                    id="late_penalty" configureLink="/config/penalties#late" :showConfigure="$latePenalty">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="late_arrival_penalty_enabled" value="true" {{ $latePenalty ? 'checked' : '' }} onchange="toggleConfigLink('late_penalty', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Early Departure') }}" description="{{ __('Penalty for early clock-out') }}"
                                    id="early_penalty" configureLink="/config/penalties#early" :showConfigure="$earlyPenalty">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="early_departure_penalty_enabled" value="true" {{ $earlyPenalty ? 'checked' : '' }} onchange="toggleConfigLink('early_penalty', this)">
                        </div>
                    </x-settings.row>


                    </div>
                </x-settings.section>
            </div> -->

            <!-- Audit and Security -->
            <!-- <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-shield" title="{{ __('Audit and Security') }}" description="{{ __('Logging and compliance') }}" />

                    <x-settings.row label="{{ __('Audit Logging') }}" description="{{ __('Track all attendance changes') }}"
                                    id="audit_logging" configureLink="/audit/logs" :showConfigure="$auditLogging">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="audit_logging_enabled" value="true" 
                                   {{ $auditLogging ? 'checked' : '' }} onchange="toggleAuditLogging(this); toggleConfigLink('audit_logging', this)">
                        </div>
                    </x-settings.row>

                        <div id="audit_config" class="mb-2 {{ $auditLogging ? '' : 'd-none' }}">
                            <x-settings.row label="{{ __('Logging Level') }}">
                                <select name="audit_level" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="minimal" {{ $auditLevel === 'minimal' ? 'selected' : '' }}>{{ __('Minimal') }}</option>
                                    <option value="standard" {{ $auditLevel === 'standard' ? 'selected' : '' }}>{{ __('Standard') }}</option>
                                    <option value="detailed" {{ $auditLevel === 'detailed' ? 'selected' : '' }}>{{ __('Detailed') }}</option>
                                    <option value="forensic" {{ $auditLevel === 'forensic' ? 'selected' : '' }}>{{ __('Forensic') }}</option>
                                </select>
                            </x-settings.row>
                        </div>

                    <x-settings.row label="{{ __('Tamper Detection') }}" description="{{ __('Detect fraudulent attendance') }}" tooltip="{{ __('Monitors for anomalies like buddy punching, GPS spoofing') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="tamper_detection_enabled" value="true" {{ $tamperDetection ? 'checked' : '' }}>
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Compliance Mode') }}" description="{{ __('SOC2, ISO, labor law compliance') }}"
                                    id="compliance" configureLink="/config/compliance" :showConfigure="$complianceMode">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="compliance_mode" value="true" {{ $complianceMode ? 'checked' : '' }} onchange="toggleConfigLink('compliance', this)">
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div> -->

            <!-- Integrations -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-link" title="{{ __('Integrations') }}" description="{{ __('Connect with other modules') }}" />

                    <x-settings.row label="{{ __('Leave Integration') }}" description="{{ __('Sync with leave management') }}"
                                    id="leave_integration" configureLink="/config/integrations#leave" :showConfigure="$leaveIntegration">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="leave_integration_enabled" value="true" {{ $leaveIntegration ? 'checked' : '' }} onchange="toggleConfigLink('leave_integration', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Holiday Calendar') }}" description="{{ __('Auto-mark holidays') }}"
                                    id="holiday_integration" configureLink="/config/holidays" :showConfigure="$holidayIntegration">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="holiday_integration_enabled" value="true" {{ $holidayIntegration ? 'checked' : '' }} onchange="toggleConfigLink('holiday_integration', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Payroll Lock') }}" description="{{ __('Lock attendance for payroll') }}"
                                    id="payroll_lock" configureLink="/config/payroll" :showConfigure="$payrollLock">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="payroll_lock_enabled" value="true" {{ $payrollLock ? 'checked' : '' }} onchange="toggleConfigLink('payroll_lock', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Payroll Export') }}" description="{{ __('Export data for payroll processing') }}"
                                    id="payroll_export" configureLink="/config/payroll#export" :showConfigure="$payrollExport">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="payroll_export_enabled" value="true" {{ $payrollExport ? 'checked' : '' }} onchange="toggleConfigLink('payroll_export', this)">
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>
        </div>

@push('page-styles')
<style>
    /* Professional Layout Adjustments */
    .content.container-fluid {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }

    /* Prevent label wrapping in small widths */
    .settings-section .form-label {
        min-width: 130px;
    }

    /* Setting blocks styling: Visible borders with standard white background */
    .settings-section {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
        background-color: #ffffff !important;
    }

    /* Target specific equalization for Time and Location Rules */
    .col-md-6.d-flex .settings-section.h-100 {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .col-md-6.d-flex .settings-section.h-100 .card-body {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 auto !important;
    }

    /* Section Header Styling: Grey background for titles only */
    .section-header-custom {
        background-color: #f8f9fa !important;
        border-radius: 0.5rem 0.5rem 0 0 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    /* Tooltip styling integration */
    [data-bs-toggle="tooltip"] {
        cursor: help;
    }
</style>
@endpush

@push('page-scripts')
<script>
    // Toggle Configuration Links visibility
    function toggleConfigLink(rowId, checkbox, invert = false) {
        const link = document.getElementById('config_link_' + rowId);
        if (link) {
            const shouldShow = invert ? !checkbox.checked : checkbox.checked;
            if (shouldShow) link.classList.remove('d-none');
            else link.classList.add('d-none');
        }
    }

    // Capture Methods Logic (Single vs Multi)
    function toggleMethodSelectionMode() {
        const isSingle = document.getElementById('single_method_only').checked;
        const checks = document.querySelectorAll('.method-check');
        
        checks.forEach(chk => {
            // Remove old listeners
            chk.onclick = null;
            
            if (isSingle) {
                // In single mode, behave like radio buttons
                chk.onclick = function() {
                    if (this.checked) {
                        checks.forEach(c => {
                            if (c !== this) {
                                c.checked = false;
                                // In single mode, if it's unchecked, hide its config link
                                const rowId = c.closest('[id]')?.id;
                                if(rowId) {
                                    const link = document.getElementById('config_link_' + rowId);
                                    if(link) link.classList.add('d-none');
                                }
                             }
                        });
                    }
                };
            }
        });

        // Validation correction: If single mode and multiple checked, keep only first
        if (isSingle) {
            let found = false;
            checks.forEach(chk => {
                if (chk.checked) {
                    if (found) chk.checked = false;
                    found = true;
                }
            });
            // If none checked, check first
            if (!found && checks.length > 0) checks[0].checked = true;
        }
    }

    // Section specific toggles
    function toggleAutoClockOut(checkbox) {
        const config = document.getElementById('auto_clockout_config');
        if (config) {
            if (checkbox.checked) config.classList.remove('d-none');
            else config.classList.add('d-none');
        }
    }
    
    function calculateMinimumHours() {
        const startTime = document.querySelector('input[name="work_day_start_time"]');
        const endTime = document.querySelector('input[name="work_day_end_time"]');
        const minHoursInput = document.getElementById('minimum_work_hours');
        
        if (!startTime || !endTime || !minHoursInput) return;
        if (!startTime.value || !endTime.value) return;
        
        // Parse times
        const [startHour, startMin] = startTime.value.split(':').map(Number);
        const [endHour, endMin] = endTime.value.split(':').map(Number);
        
        // Calculate minutes
        let startMinutes = startHour * 60 + startMin;
        let endMinutes = endHour * 60 + endMin;
        
        // Handle midnight crossing (e.g., 22:00 to 06:00)
        if (endMinutes < startMinutes) {
            endMinutes += 24 * 60; // Add 24 hours
        }
        
        // Calculate difference in hours
        const diffMinutes = endMinutes - startMinutes;
        const hours = (diffMinutes / 60).toFixed(1);
        
        // Update the field
        minHoursInput.value = hours;
        
        // Trigger auto-save
        triggerAutoSave();
    }
    // Add listeners for automatic minimum hours calculation
        const startTimeInput = document.querySelector('input[name="work_day_start_time"]');
        const endTimeInput = document.querySelector('input[name="work_day_end_time"]');
        
        if (startTimeInput) {
            startTimeInput.addEventListener('change', calculateMinimumHours);
        }
        
        if (endTimeInput) {
            endTimeInput.addEventListener('change', calculateMinimumHours);
        }
</script>
@endpush


    <!-- Late Arrival Configuration Modal -->
    <div class="modal fade" id="lateArrivalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Late Arrival Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="la la-info-circle fs-4 me-2"></i>
                            <div>
                                <strong>{{ __('Note:') }}</strong> {{ __('These settings apply globally. You can override them in Shift Templates if needed.') }}
                            </div>
                        </div>

                        <!-- Grace Period -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('Grace Period') }}</label>
                            <p class="text-muted small mb-2">{{ __('Allow employees to arrive late by this many minutes before marking as late.') }}</p>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="late_arrival_grace_period" class="form-control" value="{{ $lateArrivalGrace }}" min="0">
                                <span class="input-group-text">{{ __('Minutes') }}</span>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <!-- Penalty Configuration -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">{{ __('Penalty Action') }}</label>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="late_arrival_penalty_type" id="penalty_none" value="none" 
                                           {{ $lateArrivalPenalty === 'none' ? 'checked' : '' }} onchange="toggleDeductionFields()">
                                    <label class="form-check-label ms-2" for="penalty_none">
                                        <div class="fw-bold">{{ __('No Penalty') }}</div>
                                        <div class="text-muted small">{{ __('Just mark as "Late" in reports without further action.') }}</div>
                                    </label>
                                </div>

                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="late_arrival_penalty_type" id="penalty_warning" value="warning" 
                                           {{ $lateArrivalPenalty === 'warning' ? 'checked' : '' }} onchange="toggleDeductionFields()">
                                    <label class="form-check-label ms-2" for="penalty_warning">
                                        <div class="fw-bold">{{ __('Send Warning') }}</div>
                                        <div class="text-muted small">{{ __('Mark as "Late" and trigger a warning notification to the employee.') }}</div>
                                    </label>
                                </div>

                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="late_arrival_penalty_type" id="penalty_deduction" value="deduction" 
                                           {{ $lateArrivalPenalty === 'deduction' ? 'checked' : '' }} onchange="toggleDeductionFields()">
                                    <label class="form-check-label ms-2" for="penalty_deduction">
                                        <div class="fw-bold">{{ __('Apply Deduction') }}</div>
                                        <div class="text-muted small">{{ __('Deduct salary or leave balance based on rules.') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Deduction Settings (Conditional) -->
                        <div id="deduction_config" class="bg-light p-3 rounded border {{ $lateArrivalPenalty === 'deduction' ? '' : 'd-none' }}">
                            <h6 class="fw-bold mb-3">{{ __('Deduction Rules') }}</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Deduction Type') }}</label>
                                    <select name="late_arrival_deduction_type" class="form-select" onchange="toggleAmountField()">
                                        <option value="fixed" {{ $lateArrivalDeductionType === 'fixed' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                        <option value="per_minute" {{ $lateArrivalDeductionType === 'per_minute' ? 'selected' : '' }}>{{ __('Per Minute Amount') }}</option>
                                        <option value="percentage" {{ $lateArrivalDeductionType === 'percentage' ? 'selected' : '' }}>{{ __('Percentage of Daily Salary') }}</option>
                                        <option value="half_day" {{ $lateArrivalDeductionType === 'half_day' ? 'selected' : '' }}>{{ __('Mark as Half Day') }}</option>
                                        <option value="full_day" {{ $lateArrivalDeductionType === 'full_day' ? 'selected' : '' }}>{{ __('Mark as Absent (Full Day)') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="deduction_amount_wrapper">
                                    <label class="form-label" id="amount_label">{{ __('Amount') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="currency_symbol">$</span>
                                        <input type="number" name="late_arrival_deduction_amount" class="form-control" value="{{ $lateArrivalDeductionAmount }}" step="0.01" min="0">
                                        <span class="input-group-text d-none" id="percentage_symbol">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Changes') }}</button>
                    </div>

            </div>
        </div>
    </div>


    <!-- Early Checkout Configuration Modal -->
    <div class="modal fade" id="earlyCheckoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Early Checkout Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="la la-info-circle fs-4 me-2"></i>
                            <div>
                                <strong>{{ __('Note:') }}</strong> {{ __('These settings apply globally. You can override them in Shift Templates if needed.') }}
                            </div>
                        </div>

                        <!-- Grace Period -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('Grace Period') }}</label>
                            <p class="text-muted small mb-2">{{ __('Allow employees to leave early by this many minutes before marking as early checkout.') }}</p>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="early_checkout_grace_period" class="form-control" value="{{ $earlyCheckoutGrace }}" min="0">
                                <span class="input-group-text">{{ __('Minutes') }}</span>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <!-- Penalty Configuration -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">{{ __('Penalty Action') }}</label>
                            
                            <div class="d-flex flex-column gap-3">
                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="early_checkout_penalty_type" id="early_penalty_none" value="none" 
                                           {{ $earlyCheckoutPenalty === 'none' ? 'checked' : '' }} onchange="toggleEarlyDeductionFields()">
                                    <label class="form-check-label ms-2" for="early_penalty_none">
                                        <div class="fw-bold">{{ __('No Penalty') }}</div>
                                        <div class="text-muted small">{{ __('Just mark as "Early Checkout" in reports without further action.') }}</div>
                                    </label>
                                </div>

                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="early_checkout_penalty_type" id="early_penalty_warning" value="warning" 
                                           {{ $earlyCheckoutPenalty === 'warning' ? 'checked' : '' }} onchange="toggleEarlyDeductionFields()">
                                    <label class="form-check-label ms-2" for="early_penalty_warning">
                                        <div class="fw-bold">{{ __('Send Warning') }}</div>
                                        <div class="text-muted small">{{ __('Mark and trigger a warning notification to the employee.') }}</div>
                                    </label>
                                </div>

                                <div class="form-check card-radio p-3 border rounded">
                                    <input class="form-check-input mt-1" type="radio" name="early_checkout_penalty_type" id="early_penalty_deduction" value="deduction" 
                                           {{ $earlyCheckoutPenalty === 'deduction' ? 'checked' : '' }} onchange="toggleEarlyDeductionFields()">
                                    <label class="form-check-label ms-2" for="early_penalty_deduction">
                                        <div class="fw-bold">{{ __('Apply Deduction') }}</div>
                                        <div class="text-muted small">{{ __('Deduct salary or leave balance based on rules.') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Deduction Settings (Conditional) -->
                        <div id="early_deduction_config" class="bg-light p-3 rounded border {{ $earlyCheckoutPenalty === 'deduction' ? '' : 'd-none' }}">
                            <h6 class="fw-bold mb-3">{{ __('Deduction Rules') }}</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Deduction Type') }}</label>
                                    <select name="early_checkout_deduction_type" class="form-select" onchange="toggleEarlyAmountField()">
                                        <option value="fixed" {{ $earlyCheckoutDeductionType === 'fixed' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                        <option value="per_minute" {{ $earlyCheckoutDeductionType === 'per_minute' ? 'selected' : '' }}>{{ __('Per Minute Amount') }}</option>
                                        <option value="percentage" {{ $earlyCheckoutDeductionType === 'percentage' ? 'selected' : '' }}>{{ __('Percentage of Daily Salary') }}</option>
                                        <option value="half_day" {{ $earlyCheckoutDeductionType === 'half_day' ? 'selected' : '' }}>{{ __('Mark as Half Day') }}</option>
                                        <option value="full_day" {{ $earlyCheckoutDeductionType === 'full_day' ? 'selected' : '' }}>{{ __('Mark as Absent (Full Day)') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="early_deduction_amount_wrapper">
                                    <label class="form-label" id="early_amount_label">{{ __('Amount') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="early_currency_symbol">$</span>
                                        <input type="number" name="early_checkout_deduction_amount" class="form-control" value="{{ $earlyCheckoutDeductionAmount }}" step="0.01" min="0">
                                        <span class="input-group-text d-none" id="early_percentage_symbol">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Changes') }}</button>
                    </div>

            </div>
        </div>
    </div>


    <!-- Overtime Configuration Modal -->
    <div class="modal fade" id="overtimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Overtime Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="la la-check-circle fs-4 me-2"></i>
                            <div>
                                <strong>{{ __('Note:') }}</strong> {{ __('Overtime is calculated based on daily work hours exceeding the shift duration.') }}
                            </div>
                        </div>

                        <!-- Minimum Minutes -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('Minimum Duration') }}</label>
                            <p class="text-muted small mb-2">{{ __('Minimum extra minutes required to count as Overtime.') }}</p>
                            <div class="input-group" style="max-width: 250px;">
                                <input type="number" name="overtime_min_minutes" class="form-control" value="{{ $overtimeMinMinutes }}" min="0">
                                <span class="input-group-text">{{ __('Minutes') }}</span>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <!-- Rate Multipliers -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">{{ __('Overtime Rate Multipliers') }}</label>
                            <p class="text-muted small mb-3">{{ __('Different scenarios have different pay rates. Set the multiplier for each type.') }}</p>
                            
                            <div class="row g-3">
                                <!-- Normal OT -->
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Normal OT Rate') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">x</span>
                                        <input type="number" name="overtime_rate_normal" class="form-control" value="{{ $overtimeRateNormal }}" step="0.01" min="1">
                                    </div>
                                    <div class="form-text">{{ __('Weekday overtime (e.g., 1.25)') }}</div>
                                </div>

                                <!-- Night OT -->
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Night OT Rate') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">x</span>
                                        <input type="number" name="overtime_rate_night" class="form-control" value="{{ $overtimeRateNight }}" step="0.01" min="1" />
                                    </div>
                                    <div class="form-text">{{ __('Night shift overtime (e.g., 1.5)') }}</div>
                                </div>

                                <!-- Day Off OT -->
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Day Off OT Rate') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">x</span>
                                        <input type="number" name="overtime_rate_dayoff" class="form-control" value="{{ $overtimeRateDayOff }}" step="0.01" min="1">
                                    </div>
                                    <div class="form-text">{{ __('Weekend/day off overtime (e.g., 2.0)') }}</div>
                                </div>

                                <!-- Holiday OT -->
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Holiday OT Rate') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">x</span>
                                        <input type="number" name="overtime_rate_holiday" class="form-control" value="{{ $overtimeRateHoliday }}" step="0.01" min="1">
                                    </div>
                                    <div class="form-text">{{ __('Public holiday overtime (e.g., 2.5)') }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Changes') }}</button>
                    </div>

            </div>
        </div>
    </div>

    <!-- Web Portal Configuration Modal -->
    <div class="modal fade" id="webPortalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Web Portal Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="la la-info-circle fs-4 me-2"></i>
                            <div>
                                <strong>{{ __('Note:') }}</strong> {{ __('Configure security and validation settings for browser-based attendance.') }}
                            </div>
                        </div>

                        <!-- Security Options -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">{{ __('Security & Validation') }}</label>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="web_portal_require_gps" name="web_portal_require_gps" value="1" {{ $webPortalRequireGPS ? 'checked' : '' }}>
                                <label class="form-check-label" for="web_portal_require_gps">
                                    <strong>{{ __('Require GPS Location') }}</strong>
                                    <p class="text-muted small mb-0">{{ __('Require GPS coordinates for location tracking and geofencing.') }}</p>
                                </label>
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <!-- IP Whitelist -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">{{ __('IP Address Whitelist') }}</label>
                            <p class="text-muted small mb-2">{{ __('Restrict clock-in to specific IP addresses or ranges (comma-separated). Leave empty to allow all.') }}</p>
                            <textarea name="web_portal_ip_whitelist" class="form-control" rows="2" placeholder="e.g., 192.168.1.0/24, 10.0.0.1">{{ $webPortalIPWhitelist }}</textarea>
                            <div class="form-text">{{ __('Example: 192.168.1.0/24 (office network), 10.0.0.1 (specific IP)') }}</div>
                        </div>

                        <hr class="text-muted my-4">

                        <!-- Time Window -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">{{ __('Allowed Time Window') }}</label>
                            <p class="text-muted small mb-3">{{ __('Restrict when employees can clock in via web portal. Leave empty for 24/7 access.') }}</p>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Start Time') }}</label>
                                    <input type="time" name="web_portal_allowed_hours_start" class="form-control" value="{{ $webPortalAllowedHoursStart }}">
                                    <div class="form-text">{{ __('Earliest allowed clock-in time') }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('End Time') }}</label>
                                    <input type="time" name="web_portal_allowed_hours_end" class="form-control" value="{{ $webPortalAllowedHoursEnd }}">
                                    <div class="form-text">{{ __('Latest allowed clock-in time') }}</div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Changes') }}</button>
                    </div>

            </div>
        </div>
    </div>

    <!-- Manual Entry Configuration Modal -->
    <div class="modal fade" id="manualEntryConfigModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Manual Entry Configuration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <div class="modal-body">
                        
                        <!-- 1. Permissions -->
                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold mb-3 d-block border-bottom pb-2">{{ __('1. Who can add attendance?') }}</label>
                            
                            <div class="d-flex gap-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="manual_entry_permission_mode" id="perm_mode_roles" value="roles" 
                                           {{ $manualEntryPermissionMode == 'roles' ? 'checked' : '' }} onchange="togglePermissionFields()">
                                    <label class="form-check-label" for="perm_mode_roles">
                                        {{ __('Specific Roles') }} 
                                        <small class="text-muted d-block">{{ __('(Can add for Self AND Others)') }}</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="manual_entry_permission_mode" id="perm_mode_everyone" value="everyone" 
                                           {{ $manualEntryPermissionMode == 'everyone' ? 'checked' : '' }} onchange="togglePermissionFields()">
                                    <label class="form-check-label" for="perm_mode_everyone">
                                        {{ __('Everyone (Self-Service)') }}
                                        <small class="text-muted d-block">{{ __('(Can add for Self ONLY)') }}</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3 ps-4 border-start border-3" id="allowedRolesDiv">
                                <label class="form-label">{{ __('Select Allowed Roles') }}</label>
                                <select class="form-select select2" name="manual_entry_allowed_roles[]" multiple data-placeholder="{{ __('Select Roles') }}">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, $manualEntryAllowedRoles) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 2. Approval Workflow -->
                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold mb-3 d-block border-bottom pb-2">{{ __('2. Approval Workflow') }}</label>
                            
                            <div class="mb-3">
                                <label class="form-label">{{ __('Approval Policy') }}</label>
                                <select class="form-select" name="manual_entry_approval_policy" id="manualEntryApprovalPolicy" onchange="toggleApprovalLogic()">
                                    <option value="auto_approve" {{ $manualEntryApprovalPolicy == 'auto_approve' ? 'selected' : '' }}>{{ __('Auto Approve (No Review Needed)') }}</option>
                                    <option value="manual_approval" {{ $manualEntryApprovalPolicy == 'manual_approval' ? 'selected' : '' }}>{{ __('Require Approval') }}</option>
                                </select>
                            </div>

                            <!-- Nested Approval Logic -->
                            <div id="approvalLogic" class="ps-4 border-start border-3" style="display: none; background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Structure') }}</label>
                                        <select class="form-select" name="manual_entry_approval_structure" id="approvalStructure" onchange="toggleEntitySelects()">
                                            <option value="single" {{ $manualEntryApprovalStructure == 'single' ? 'selected' : '' }}>{{ __('Single Person/Role') }}</option>
                                            <option value="hierarchical" {{ $manualEntryApprovalStructure == 'hierarchical' ? 'selected' : '' }}>{{ __('Hierarchical Chain') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Approver Entity') }}</label>
                                        <select class="form-select" name="manual_entry_approver_entity" id="approverEntity" onchange="toggleEntitySelects()">
                                            <option value="role" {{ $manualEntryApproverEntity == 'role' ? 'selected' : '' }}>{{ __('Role') }}</option>
                                            <option value="individual" {{ $manualEntryApproverEntity == 'individual' ? 'selected' : '' }}>{{ __('Individual User') }}</option>
                                        </select>
                                    </div>

                                    <!-- Single Selects -->
                                    <div class="col-12" id="entityRoleDiv" style="display: none;">
                                        <label class="form-label">{{ __('Select Approver Role') }}</label>
                                        <select class="form-select select2" name="manual_entry_approver_role_id" data-placeholder="{{ __('Choose ONE Role') }}">
                                            <option value="">{{ __('Select Role...') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $manualEntryApproverRoleId == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12" id="entityUserDiv" style="display: none;">
                                        <label class="form-label">{{ __('Select Approver User') }}</label>
                                        <select class="form-select select2" name="manual_entry_approver_user_id" data-placeholder="{{ __('Choose ONE User') }}">
                                            <option value="">{{ __('Select User...') }}</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $manualEntryApproverUserId == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Hierarchical Builders -->
                                    <div class="col-12" id="hierarchicalRoleWrapper" style="display: none;">
                                        <label class="form-label mb-2">{{ __('Approver Chain (Ordered)') }}</label>
                                        <div id="hierarchicalRoleList" class="d-flex flex-column gap-2 mb-2">
                                            <!-- Dynamic Rows Will Be Injected Here -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="border-style: dashed;" onclick="addHierarchicalRow('role')">
                                            <i class="fas fa-plus-circle me-1"></i> {{ __('Add Next Approver') }}
                                        </button>
                                        <div class="form-text mt-1">{{ __('Define the approval chain from first to last.') }}</div>
                                    </div>

                                    <div class="col-12" id="hierarchicalUserWrapper" style="display: none;">
                                        <label class="form-label mb-2">{{ __('Approver Chain (Ordered)') }}</label>
                                        <div id="hierarchicalUserList" class="d-flex flex-column gap-2 mb-2">
                                            <!-- Dynamic Rows Will Be Injected Here -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="border-style: dashed;" onclick="addHierarchicalRow('user')">
                                            <i class="fas fa-plus-circle me-1"></i> {{ __('Add Next Approver') }}
                                        </button>
                                        <div class="form-text mt-1">{{ __('Define the approval chain from first to last.') }}</div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- 3. Validation -->
                        <div class="mb-4 text-start">
                             <label class="form-label fw-bold mb-3 d-block border-bottom pb-2">{{ __('3. Data & Validation') }}</label>

                            <!-- DATA PASSING FOR JS -->
                            <script>
                                window.attendanceConfig = {
                                    roles: @json($roles),
                                    users: @json($users->map(fn($u) => ['id' => $u->id, 'text' => $u->name . ' (' . $u->email . ')'])),
                                    savedHierarchicalRoles: @json($manualEntryHierarchicalRoleIds),
                                    savedHierarchicalUsers: @json($manualEntryHierarchicalUserIds)
                                };
                            </script>

                            <style>
                                /* Fix Select2 Auto-select on click by ensuring dropdown doesn't overlap input */
                                .select2-container .select2-dropdown--below {
                                    margin-top: 4px !important;
                                }
                                .select2-container .select2-dropout--above {
                                    margin-bottom: 4px !important;
                                }
                                /* Ensure Select2 dropdown appears above modal when attached to body */
                                .select2-dropdown-in-modal {
                                    z-index: 9999 !important;
                                }
                            </style>
                            
                            <!-- Project Tracking -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                     <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="manual_entry_track_project" name="manual_entry_track_project" value="1" {{ $manualEntryTrackProject ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_entry_track_project">
                                            <strong>{{ __('Track Project') }}</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="manual_entry_require_project" name="manual_entry_require_project" value="1" {{ $manualEntryRequireProject ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_entry_require_project">
                                            <strong>{{ __('Require Project') }}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-3 opacity-25">

                            <!-- Reason & Format -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="manual_entry_require_reason" name="manual_entry_require_reason" value="1" {{ $manualEntryRequireReason ? 'checked' : '' }}>
                                <label class="form-check-label" for="manual_entry_require_reason">
                                    <strong>{{ __('Require Reason/Remark') }}</strong>
                                </label>
                            </div>
                            
                            <!-- Limits -->
                             <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Max Days Back') }}</label>
                                    <input type="number" class="form-control" name="manual_entry_max_days_back" value="{{ $manualEntryMaxDaysBack }}" min="0">
                                </div>
                                 <div class="col-md-6 pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="manual_entry_allow_future" name="manual_entry_allow_future" value="1" {{ $manualEntryAllowFuture ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual_entry_allow_future">
                                            <strong>{{ __('Allow Future Dates') }}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Configuration') }}</button>
                    </div>
            </div>
        </div>
    </div>


    <!-- Missed Punch Configuration Modal -->
    <div class="modal fade" id="missedPunchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Missed Punch Rules & Workflow') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <p class="text-muted small mb-4">
                            {{ __('Define how employees can request corrections for missed attendance records and who approves them.') }}
                        </p>

                        <!-- Retroactive Limit -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <label class="form-label fw-bold text-dark mb-1">{{ __('Retroactive Submission Limit') }} <span class="text-danger">*</span></label>
                                            <p class="text-muted small mb-0">{{ __('Maximum number of days in the past a request can be submitted.') }}</p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="input-group" style="width: 150px;">
                                                <input type="number" name="missed_punch_retroactive_limit" class="form-control" 
                                                       value="{{ $missedPunchRetroactiveLimit }}" min="0" max="30" required>
                                                <span class="input-group-text bg-white border-start-0">{{ __('Days') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Limit -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <label class="form-label fw-bold text-dark mb-1">{{ __('Monthly Request Limit') }} <span class="text-danger">*</span></label>
                                            <p class="text-muted small mb-0">{{ __('Maximum number of missed punch requests can make per month.') }}</p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="input-group" style="width: 150px;">
                                                <input type="number" name="missed_punch_max_requests_per_month" class="form-control" 
                                                       value="{{ $missedPunchMaxRequests }}" min="0" max="31" required>
                                                <span class="input-group-text bg-white border-start-0">{{ __('Qty') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason Requirement -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="missed_punch_require_reason" name="missed_punch_require_reason" value="true" 
                                               {{ $missedPunchRequireReason ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="missed_punch_require_reason">{{ __('Mandatory Reason Submission') }}</label>
                                        <p class="text-muted small mb-0">{{ __('Employees must provide a reason for every correction request.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approval Mode -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border shadow-none">
                                    <div class="card-body">
                                        <h6 class="fw-bold d-flex align-items-center gap-2 mb-3">
                                            <i class="la la-user-check text-info"></i>
                                            {{ __('Approval Workflow Routing') }}
                                        </h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">{{ __('Select Approval Strategy') }}</label>
                                                <select name="missed_punch_approval_mode" class="form-select">
                                                    <option value="manager" {{ $missedPunchApprovalMode === 'manager' ? 'selected' : '' }}>
                                                        {{ __('Direct Manager') }}
                                                    </option>
                                                    <option value="hr" {{ $missedPunchApprovalMode === 'hr' ? 'selected' : '' }}>
                                                        {{ __('HR Administrator') }}
                                                    </option>
                                                    <option value="multi" {{ $missedPunchApprovalMode === 'multi' ? 'selected' : '' }}>
                                                        {{ __('Multi-Level Approval') }}
                                                    </option>
                                                </select>
                                                <div class="form-text small mt-2">
                                                    {{ __('Define who reviews and approves missed punch requests.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Changes') }}</button>
                    </div>

            </div>
        </div>
    </div>

    <!-- Missing Punch Configuration Modal -->
    <style>
        /* CSS-Only Tooltip (Robust Fallback) */
        .custom-tooltip {
            position: relative;
            cursor: pointer;
            display: inline-block;
        }
        .custom-tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            line-height: 1.4;
            white-space: normal;
            width: 220px;
            text-align: center;
            z-index: 1000020; /* Extremely high z-index */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 8px;
            pointer-events: none;
        }
        /* Arrow */
        .custom-tooltip:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #333;
            margin-bottom: -4px;
            z-index: 1000020;
        }
    </style>
    <div class="modal fade" id="missingPunchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="la la-clock me-2 text-primary"></i>
                        {{ __('Missing Clock-In/Out Configuration') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.attendance-settings.missed-punch.update') }}" method="POST">
                    @csrf
                    <div class="modal-body p-0">
                        <div class="row g-0">
                            <!-- Sidebar/Tabs -->
                            <div class="col-md-3 bg-light border-end">
                                <div class="nav flex-column nav-pills p-3" id="missing-punch-tab" role="tablist" aria-orientation="vertical">
                                    <button class="nav-link active text-start mb-1" id="mp-detection-tab" data-bs-toggle="pill" data-bs-target="#mp-detection" type="button" role="tab">
                                        <i class="la la-search me-2"></i> {{ __('Detection') }}
                                    </button>
                                    <button class="nav-link text-start mb-1" id="mp-handling-tab" data-bs-toggle="pill" data-bs-target="#mp-handling" type="button" role="tab">
                                        <i class="la la-cogs me-2"></i> {{ __('Handling') }}
                                    </button>
                                    <button class="nav-link text-start mb-1" id="mp-auto-tab" data-bs-toggle="pill" data-bs-target="#mp-auto" type="button" role="tab">
                                        <i class="la la-magic me-2"></i> {{ __('Correction') }}
                                    </button>
                                    <button class="nav-link text-start" id="mp-penalties-tab" data-bs-toggle="pill" data-bs-target="#mp-penalties" type="button" role="tab">
                                        <i class="la la-gavel me-2"></i> {{ __('Penalties') }}
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="col-md-9">
                                <div class="tab-content p-4" id="missing-punch-tabContent">
                                    
                                    <!-- Detection Tab -->
                                    <div class="tab-pane fade show active" id="mp-detection" role="tabpanel">
                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Detection & Monitoring') }}</h6>
                                        
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="missing_punch_auto_detect" name="missing_punch_auto_detect" value="true" {{ $missingPunchSettings['auto_detect'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="missing_punch_auto_detect">{{ __('Auto-Detect Missing Punches') }}</label>
                                            <div class="form-text mt-0">{{ __('Automatically identify incomplete records.') }}</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('Grace Period (Minutes)') }}</label>
                                            <input type="number" name="missing_punch_grace_period" class="form-control" value="{{ $missingPunchSettings['grace_period'] }}" min="0" max="240" required>
                                            <div class="form-text">{{ __('Time after shift end before marking as missing.') }}</div>
                                        </div>

                                        <div class="border rounded p-3 bg-light">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" role="switch" id="missing_punch_notification_enabled" name="missing_punch_notification_enabled" value="true" {{ $missingPunchSettings['notification_enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="missing_punch_notification_enabled">{{ __('Enable Notifications') }}</label>
                                            </div>
                                            <div class="ps-4">
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox" name="missing_punch_notify_employee" value="true" {{ $missingPunchSettings['notify_employee'] ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ __('Notify Employee') }}</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="missing_punch_notify_supervisor" value="true" {{ $missingPunchSettings['notify_supervisor'] ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ __('Notify Supervisor') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Configuration') }}</button>
                    </div>

            </div>
        </div>
    </div>

    <!-- Attendance Corrections Configuration Modal -->
    <div class="modal fade" id="attendanceCorrectionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Attendance Correction Policies') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                        <p class="text-muted small mb-4">
                            {{ __('Configure how admins and managers can directly modify attendance records.') }}
                        </p>

                        <!-- Retroactive Limit -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <label class="form-label fw-bold text-dark mb-1">{{ __('Retroactive Limit (Days)') }}</label>
                                            <p class="text-muted small mb-0">{{ __('How many days back can an admin or manager modify attendance? (0 = No limit)') }}</p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="input-group" style="width: 150px;">
                                                <input type="number" name="correction_retroactive_limit" class="form-control" 
                                                       value="{{ $correctionRetroactiveLimit }}" min="0" max="365" required>
                                                <span class="input-group-text bg-white border-start-0">{{ __('Days') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Require Reason -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="correction_require_reason" name="correction_require_reason" value="true" 
                                               {{ $correctionRequireReason ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="correction_require_reason">{{ __('Require Reason for Correction') }}</label>
                                        <p class="text-muted small mb-0">{{ __('Force the admin to provide an explanation for every manual change.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Audit Trail -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="correction_audit_trail_enabled" name="correction_audit_trail_enabled" value="true" 
                                               {{ $correctionAuditTrail ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="correction_audit_trail_enabled">{{ __('Enable Advanced Audit Trail') }}</label>
                                        <p class="text-muted small mb-0">{{ __('Keep a detailed log of original vs corrected times and who made the change.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary" onclick="submitAttendanceSettings(false)">{{ __('Save Configuration') }}</button>
                    </div>

            </div>
        </div>
    </div>

</form>

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- LOGIC FROM FIRST BLOCK ---
        window.toggleConfigLink = function(rowId, checkbox, invert = false) {
            const link = document.getElementById('config_link_' + rowId);
            if (link) {
                const shouldShow = invert ? !checkbox.checked : checkbox.checked;
                if (shouldShow) link.classList.remove('d-none');
                else link.classList.add('d-none');
            }
        }

        window.toggleMethodSelectionMode = function() {
            const isSingle = document.getElementById('single_method_only').checked;
            const checks = document.querySelectorAll('.method-check');
            checks.forEach(chk => {
                chk.onclick = null;
                if (isSingle) {
                    chk.onclick = function() {
                        if (this.checked) {
                            checks.forEach(c => {
                                if (c !== this) {
                                    c.checked = false;
                                    const rowId = c.closest('[id]')?.id;
                                    if(rowId) {
                                        const link = document.getElementById('config_link_' + rowId);
                                        if(link) link.classList.add('d-none');
                                    }
                                 }
                            });
                        }
                    };
                }
            });
            if (isSingle) {
                let found = false;
                checks.forEach(chk => {
                    if (chk.checked) {
                        if (found) chk.checked = false;
                        found = true;
                    }
                });
                if (!found && checks.length > 0) checks[0].checked = true;
            }
        }

        window.toggleAutoClockOut = function(checkbox) {
            const config = document.getElementById('auto_clockout_config');
            if (config) {
                if (checkbox.checked) config.classList.remove('d-none');
                else config.classList.add('d-none');
            }
        }

        window.toggleShifts = function(checkbox) {
            const config = document.getElementById('shift_config');
            if (config) {
                if (checkbox.checked) config.classList.remove('d-none');
                else config.classList.add('d-none');
            }
        }

        window.showToast = function(message, type = 'success') {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        }

        window.toggleGraceIn = function(checkbox) {
            const inputContainer = document.getElementById('grace_in_input');
            if (inputContainer) {
                const input = inputContainer.querySelector('input[name="grace_in_minutes"]');
                if (input) {
                    if (checkbox.checked) {
                        input.removeAttribute('readonly');
                        input.removeAttribute('style');
                        input.classList.remove('text-muted');
                    } else {
                        input.setAttribute('readonly', 'readonly');
                        input.setAttribute('style', 'background-color:#e9ecef;');
                        input.classList.add('text-muted');
                    }
                }
            }
        }

        window.toggleGraceOut = function(checkbox) {
            const inputContainer = document.getElementById('grace_out_input');
            if (inputContainer) {
                const input = inputContainer.querySelector('input[name="grace_out_minutes"]');
                if (input) {
                    if (checkbox.checked) {
                        input.removeAttribute('readonly');
                        input.removeAttribute('style');
                        input.classList.remove('text-muted');
                    } else {
                        input.setAttribute('readonly', 'readonly');
                        input.setAttribute('style', 'background-color:#e9ecef;');
                        input.classList.add('text-muted');
                    }
                }
            }
        }

        const workingDayCheckboxes = document.querySelectorAll('.working-day-input');
        workingDayCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                triggerAutoSave();
            });
        });

        if (typeof window.autoSaveTimeout === 'undefined') {
            window.autoSaveTimeout = null;
        }

        // --- LOGIC FROM SECOND BLOCK ---
        const getCheckedValue = (name) => {
            const el = document.querySelector(`input[name="${name}"]:checked`);
            return el ? el.value : null;
        };
    });
</script>
@endpush

                                    <!-- Handling Tab -->
                                    <div class="tab-pane fade" id="mp-handling" role="tabpanel">
                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Handling & Actions') }}</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                {{ __('Default Action') }}
                                                <i class="la la-info-circle text-primary ms-1 custom-tooltip" data-tooltip="{{ __('Determines the Attendance Status (e.g., Absent, Half Day) recorded for the day.') }}"></i>
                                            </label>
                                            <select name="missing_punch_action" class="form-select">
                                                <option value="mark_absent" {{ $missingPunchSettings['action'] === 'mark_absent' ? 'selected' : '' }}>{{ __('Mark as Absent') }}</option>
                                                <option value="half_day" {{ $missingPunchSettings['action'] === 'half_day' ? 'selected' : '' }}>{{ __('Mark as Half Day') }}</option>
                                                <option value="request_clarification" {{ $missingPunchSettings['action'] === 'request_clarification' ? 'selected' : '' }}>{{ __('Request Clarification') }}</option>
                                                <option value="auto_approve" {{ $missingPunchSettings['action'] === 'auto_approve' ? 'selected' : '' }}>{{ __('Auto-Approve Full Day') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="missing_punch_allow_backdated" name="missing_punch_allow_backdated" value="true" {{ $missingPunchSettings['allow_backdated'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="missing_punch_allow_backdated">
                                                {{ __('Allow Backdated Punches') }}
                                                <i class="la la-info-circle text-primary ms-1 custom-tooltip" data-tooltip="{{ __('Allows employees to submit attendance requests for past dates they forgot to clock in.') }}"></i>
                                            </label>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">{{ __('Backdate Limit (Days)') }}</label>
                                                <input type="number" name="missing_punch_backdate_limit_days" class="form-control" value="{{ $missingPunchSettings['backdate_limit_days'] }}" min="0" max="30">
                                            </div>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="missing_punch_require_reason" name="missing_punch_require_reason" value="true" {{ $missingPunchSettings['require_reason'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="missing_punch_require_reason">{{ __('Require Reason for Correction') }}</label>
                                        </div>
                                    </div>

                                    <!-- Auto-Correction Tab -->
                                    <div class="tab-pane fade" id="mp-auto" role="tabpanel">
                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Auto-Correction') }}</h6>
                                        
                                        <div class="alert alert-info border-0 shadow-sm mb-4">
                                            <div class="d-flex gap-2">
                                                <i class="la la-info-circle mt-1"></i>
                                                <small>{{ __('System attempts to match orphaned punches within a threshold. Useful if employee forgot one punch.') }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="missing_punch_auto_pair" name="missing_punch_auto_pair" value="true" {{ $missingPunchSettings['auto_pair'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="missing_punch_auto_pair">
                                                {{ __('Auto-Pair Orphaned Punches') }}
                                                <i class="la la-info-circle text-primary ms-1 custom-tooltip" data-tooltip="{{ __('An "Orphan" is a punch without a partner (In without Out). This setting tries to intelligently close incomplete records.') }}"></i>
                                            </label>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                {{ __('Pairing Threshold (Minutes)') }}
                                                <i class="la la-info-circle text-primary ms-1 custom-tooltip" data-tooltip="{{ __('How close a punch must be to the shift end to be considered a match. Punches outside this window stay as orphans.') }}"></i>
                                            </label>
                                            <input type="number" name="missing_punch_auto_pair_threshold" class="form-control" value="{{ $missingPunchSettings['auto_pair_threshold'] }}" min="15" max="480">
                                            <div class="form-text">{{ __('Max gap between punch and shift time to consider a match.') }}</div>
                                        </div>
                                    </div>

                                    <!-- Penalties Tab -->
                                    <div class="tab-pane fade" id="mp-penalties" role="tabpanel">
                                        <h6 class="fw-bold mb-3 text-primary">{{ __('Penalties & Deductions') }}</h6>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                {{ __('Deduction Type') }}
                                                <i class="la la-info-circle text-primary ms-1 custom-tooltip" data-tooltip="{{ __('Financial fine applied ON TOP of the attendance status.') }}"></i>
                                            </label>
                                            <select name="missing_punch_deduction_type" class="form-select">
                                                <option value="none" {{ $missingPunchSettings['deduction_type'] === 'none' ? 'selected' : '' }}>{{ __('No Deduction') }}</option>
                                                <option value="fixed" {{ $missingPunchSettings['deduction_type'] === 'fixed' ? 'selected' : '' }}>{{ __('Fixed Amount') }}</option>
                                                <option value="percentage" {{ $missingPunchSettings['deduction_type'] === 'percentage' ? 'selected' : '' }}>{{ __('Percentage of Daily Wage') }}</option>
                                                <option value="hourly" {{ $missingPunchSettings['deduction_type'] === 'hourly' ? 'selected' : '' }}>{{ __('Hourly Rate Deduction') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('Deduction Amount') }}</label>
                                            <input type="number" step="0.01" name="missing_punch_deduction_amount" class="form-control" value="{{ $missingPunchSettings['deduction_amount'] }}" min="0">
                                            <div class="form-text">{{ __('Amount or Percentage based on type.') }}</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('Max Occurrences / Month') }}</label>
                                            <input type="number" name="missing_punch_max_occurrences" class="form-control" value="{{ $missingPunchSettings['max_occurrences'] }}" min="0" max="31">
                                            <div class="form-text">{{ __('Occurrences before escalation/review is triggered.') }}</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>

        </div>
    </div>

@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        

        window.submitAttendanceSettings = function(silent = false) {
            const form = document.getElementById('attendance-settings-form');
            if (!form) {
                return;
            }
            let formData = new FormData(form);
            const submitBtn = document.querySelector('button[form="attendance-settings-form"]');
            
            if (!formData.has('allowed_methods[]') && !formData.has('allowed_methods')) {
                formData.append('allowed_methods', '[]');
            }
            if (!formData.has('working_days[]') && !formData.has('working_days')) {
                formData.append('working_days', '[]');
            }

            // Fields that belong to modals and should NOT be submitted with the main form
            const modalFields = [
                // Overtime Config Modal fields
                'overtime_min_minutes', 'overtime_rate_normal', 'overtime_rate_night', 
                'overtime_rate_dayoff', 'overtime_rate_holiday',
                
                // Manual Entry Config Modal fields
                'manual_entry_permission_mode', 'manual_entry_allowed_roles',
                'manual_entry_approval_policy', 'manual_entry_approval_structure',
                'manual_entry_approver_entity', 'manual_entry_approver_role_id',
                'manual_entry_approver_user_id', 'manual_entry_hierarchical_role_ids',
                'manual_entry_hierarchical_user_ids', 'manual_entry_track_project',
                'manual_entry_require_project', 'manual_entry_require_reason',
                'manual_entry_max_days_back', 'manual_entry_allow_future',
                
                // Web Portal Config Modal fields
                'web_portal_require_gps', 'web_portal_ip_whitelist',
                'web_portal_allowed_hours_start', 'web_portal_allowed_hours_end',
                
                // Missing Punch Config Modal fields
                'missing_punch_action', 'missing_punch_deduction_type',
                'missing_punch_deduction_amount', 'missing_punch_auto_pair_threshold',
                'missing_punch_backdate_limit_days', 'missing_punch_max_occurrences',
                'missing_punch_auto_detect', 'missing_punch_auto_pair',
                'missing_punch_notification_enabled', 'missing_punch_notify_employee',
                'missing_punch_notify_supervisor', 'missing_punch_allow_backdated',
                'missing_punch_require_reason', 'missing_punch_grace_period',
                
                // Late Arrival Config Modal fields
                'late_arrival_grace_period', 'late_arrival_penalty_type',
                'late_arrival_deduction_type', 'late_arrival_deduction_amount',
                
                // Early Checkout Config Modal fields
                'early_checkout_grace_period', 'early_checkout_penalty_type',
                'early_checkout_deduction_type', 'early_checkout_deduction_amount',
                
                // Missed Punch Approval Modal fields
                'missed_punch_retroactive_limit', 'missed_punch_max_requests_per_month',
                'missed_punch_require_reason', 'missed_punch_approval_mode',
                
                // Correction Config fields
                'correction_retroactive_limit', 'correction_require_reason',
                'correction_audit_trail_enabled',
                
                // Shift Config fields
                'shift_mode'
            ];

            // Context-Aware Filtering (Smart Mode v2.2)
            let cleanedFormData = new FormData();
            let filteredCount = 0;

            const activeModal = document.querySelector('.modal.show');
            let allowedPrefixes = [];

            if (activeModal) {
                const id = activeModal.id;
                
                if (id === 'manualEntryConfigModal') allowedPrefixes.push('manual_entry_');
                else if (id === 'missedPunchModal') allowedPrefixes.push('missed_punch_');
                else if (id === 'overtimeModal') allowedPrefixes.push('overtime_');
                else if (id === 'attendanceCorrectionsModal') allowedPrefixes.push('correction_');
                // Assumed IDs for others based on pattern, can be adjusted if needed
                else if (id === 'webPortalConfigModal') allowedPrefixes.push('web_portal_');
                else if (id === 'lateArrivalConfigModal') allowedPrefixes.push('late_arrival_');
                else if (id === 'earlyCheckoutConfigModal') allowedPrefixes.push('early_checkout_');
            } else {
            }

            const modalPrefixes = [
                'manual_entry_',
                'overtime_',
                'web_portal_',
                'late_arrival_',
                'early_checkout_',
                'correction_',
                'missed_punch_' // Added back for completeness
            ];

            for (let [key, value] of formData.entries()) {
                const baseKey = key.endsWith('[]') ? key.slice(0, -2) : key;
                let shouldFilter = false;
                
                // 1. Check if it is a known modal field (by list or prefix)
                let isModalField = false;
                if (modalFields.includes(key) || modalFields.includes(baseKey)) {
                    isModalField = true;
                } else {
                    for (let prefix of modalPrefixes) {
                        if (key.startsWith(prefix)) {
                            isModalField = true;
                            break;
                        }
                    }
                }

                if (isModalField) {
                    // 2. If it IS a modal field, check if it is ALLOWED by current context
                    let isAllowed = false;
                    for (let allowed of allowedPrefixes) {
                        if (key.startsWith(allowed)) {
                            isAllowed = true;
                            break;
                        }
                    }
                    
                    if (!isAllowed) {
                        shouldFilter = true;
                    }
                }

                if (!shouldFilter) {
                    cleanedFormData.append(key, value);
                    // console.log(`  ✅ Keeping: ${key}`);
                } else {
                    filteredCount++;
                    // console.log(`  🚫 Filtered out: ${key}`);
                }
            }
            formData = cleanedFormData;

            // Explicitly handle unchecked checkboxes so they are sent as 'false'
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (cb.name && !cb.name.endsWith('[]') && !formData.has(cb.name) && !modalFields.includes(cb.name)) {
                    formData.append(cb.name, 'false');
                }
            });

            // Flag to tell the backend this is a partial update (so it doesn't try to validate missing fields)
            // Appending at the end to ensure it's preserved
            formData.append('is_partial', '1');


            if (!silent && submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="la la-spinner la-spin"></i> ' + '{{ __("Saving...") }}';
            }

            fetch('{{ route("admin.attendance-settings.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success && !silent) {
                    showToast(data.message || '{{ __("Attendance settings updated successfully") }}', 'success');
                } else if (!data.success && !silent) {
                    if (data.errors) {
                        // Show each validation error
                        Object.entries(data.errors).forEach(([field, errors]) => {
                            // Error logged for debugging
                        });
                    }
                    showToast(data.message || '{{ __("Failed to save settings") }}', 'error');
                }
            })
            .catch(error => {
                if (!silent) showToast('Error saving settings', 'error');
            })
            .finally(() => {
                if (!silent && submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="la la-save"></i> ' + '{{ __("Save Changes") }}';
                }
            });
        };


        window.triggerAutoSave = function() {
            if (window.autoSaveTimeout) clearTimeout(window.autoSaveTimeout);
            window.autoSaveTimeout = setTimeout(() => {
                submitAttendanceSettings(true);
            }, 800);
        }

        // --- LOGIC FROM SECOND BLOCK ---
        const getCheckedValue = (name) => {
            const el = document.querySelector(`input[name="${name}"]:checked`);
            return el ? el.value : null;
        };

        const safeToggleClass = (id, className, condition) => {
            const el = document.getElementById(id);
            if (el) {
                if (condition) {
                    el.classList.remove(className);
                } else {
                    el.classList.add(className);
                }
            }
        };

        /*
         * Generic Save & UI Toggle Function
         */
        window.toggleConfigLink = function(id, element) {
            const isChecked = element.checked;
            
            // 1. UI Update for Config Link (if exists)
            const configLink = document.getElementById('config_link_' + id);
            if (configLink) {
                if (isChecked) {
                    configLink.classList.remove('d-none');
                } else {
                    configLink.classList.add('d-none');
                }
            }

            // 2. AJAX Save
            const key = element.getAttribute('name');
            const isArrayField = key.endsWith('[]');
            
            const formData = new FormData();
            
            // For array checkboxes, collect ALL currently checked values
            if (isArrayField) {
                const baseName = key.replace('[]', '');
                const allCheckboxes = document.querySelectorAll(`input[name="${key}"]`);
                const checkedValues = Array.from(allCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                // Send all checked values (or empty array if none checked)
                if (checkedValues.length > 0) {
                    checkedValues.forEach(val => formData.append(key, val));
                } else {
                    // Send empty array
                    formData.append(baseName, '[]');
                }
            } else {
                // For regular boolean toggles, send 1/0
                const value = isChecked ? 1 : 0;
                formData.append(key, value);
            }
            
            formData.append('_method', 'PUT'); 
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('is_partial', '1'); // Trigger partial update logic

            fetch('{{ route("admin.attendance-settings.update") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message || '{{ __("Settings updated successfully") }}');
                } else {
                    toastr.error(data.message || '{{ __("Failed to update settings") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('{{ __("An error occurred") }}');
            });
        };

        /*
         * Specific UI Toggles
         */
        window.toggleAutoClockOut = function(element) {
            const configDiv = document.getElementById('auto_clockout_config');
            if (configDiv) {
                if (element.checked) {
                    configDiv.classList.remove('d-none');
                } else {
                    configDiv.classList.add('d-none');
                }
            }
        };

        window.toggleGeofencing = function(element) {
            const configDiv = document.getElementById('geofencing_config');
            if (configDiv) {
                if (element.checked) {
                    configDiv.classList.remove('d-none');
                } else {
                    configDiv.classList.add('d-none');
                }
            }
        };

        window.toggleAuditLogging = function(element) {
            const configDiv = document.getElementById('audit_config');
            if (configDiv) {
                if (element.checked) {
                    configDiv.classList.remove('d-none');
                } else {
                    configDiv.classList.add('d-none');
                }
            }
        };

        // --- LATE ARRIVAL LOGIC ---
        window.toggleDeductionFields = function() {
            const type = getCheckedValue('late_arrival_penalty_type');
            safeToggleClass('deduction_config', 'd-none', type === 'deduction');
        };

        window.toggleAmountField = function() {
            const typeEl = document.querySelector('select[name="late_arrival_deduction_type"]');
            if (!typeEl) return;
            const type = typeEl.value;
            const wrapper = document.getElementById('deduction_amount_wrapper');
            const currency = document.getElementById('currency_symbol');
            const percentage = document.getElementById('percentage_symbol');
            if (!wrapper || !currency || !percentage) return;
            if (type === 'half_day' || type === 'full_day') {
                wrapper.classList.add('d-none');
            } else {
                wrapper.classList.remove('d-none');
                if (type === 'percentage') {
                    currency.classList.add('d-none');
                    percentage.classList.remove('d-none');
                } else {
                    currency.classList.remove('d-none');
                    percentage.classList.add('d-none');
                }
            }
        };

        window.toggleEarlyDeductionFields = function() {
            const type = getCheckedValue('early_checkout_penalty_type');
            safeToggleClass('early_deduction_config', 'd-none', type === 'deduction');
        };

        window.toggleEarlyAmountField = function() {
            const typeEl = document.querySelector('select[name="early_checkout_deduction_type"]');
            if (!typeEl) return;
            const type = typeEl.value;
            const wrapper = document.getElementById('early_deduction_amount_wrapper');
            const currency = document.getElementById('early_currency_symbol');
            const percentage = document.getElementById('early_percentage_symbol');
            if (!wrapper || !currency || !percentage) return;
            if (type === 'half_day' || type === 'full_day') {
                wrapper.classList.add('d-none');
            } else {
                wrapper.classList.remove('d-none');
                if (type === 'percentage') {
                    currency.classList.add('d-none');
                    percentage.classList.remove('d-none');
                } else {
                    currency.classList.remove('d-none');
                    percentage.classList.add('d-none');
                }
            }
        };

        const openModal = (modalId, callback) => {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            if (typeof bootstrap === 'undefined') {
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $(modalEl).modal('show');
                    if(callback) callback();
                }
                return;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            if(callback) callback();
            modal.show();
        };

        const applyModalBinding = (linkId, modalId, preOpen) => {
            const link = document.getElementById(linkId);
            if (link) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal(modalId, preOpen);
                });
            }
        };

        applyModalBinding('config_link_late_arrival', 'lateArrivalModal', () => { toggleDeductionFields(); toggleAmountField(); });
        applyModalBinding('config_link_early_checkout', 'earlyCheckoutModal', () => { toggleEarlyDeductionFields(); toggleEarlyAmountField(); });
        applyModalBinding('config_link_overtime', 'overtimeModal');
        applyModalBinding('config_link_missed_punch', 'missedPunchModal');
        applyModalBinding('config_link_corrections', 'attendanceCorrectionsModal');
        applyModalBinding('config_link_overtime_approval', 'overtimeModal');
        applyModalBinding('config_link_web_portal', 'webPortalModal');

        window.togglePermissionFields = function() {
            const mode = document.querySelector('input[name="manual_entry_permission_mode"]:checked').value;
            const rolesDiv = document.getElementById('allowedRolesDiv');
            if(rolesDiv) rolesDiv.style.display = (mode === 'roles') ? 'block' : 'none';
        };

        window.toggleApprovalLogic = function() {
            const policy = document.getElementById('manualEntryApprovalPolicy').value;
            const logicDiv = document.getElementById('approvalLogic');
            if(logicDiv) logicDiv.style.display = (policy === 'manual_approval') ? 'block' : 'none';
            if(policy === 'manual_approval') toggleEntitySelects();
        };

        window.isHierarchicalInit = false;
        window.addHierarchicalRow = function(type, selectedValue = null) {
            const listId = type === 'role' ? 'hierarchicalRoleList' : 'hierarchicalUserList';
            const listEl = document.getElementById(listId);
            const inputName = type === 'role' ? 'manual_entry_hierarchical_role_ids[]' : 'manual_entry_hierarchical_user_ids[]';
            const data = type === 'role' ? window.attendanceConfig.roles : window.attendanceConfig.users;
            const rowId = 'h_row_' + Date.now() + Math.floor(Math.random() * 1000);
            let optionsHtml = '<option value=""></option>';
            data.forEach(item => {
                const isSelected = selectedValue == item.id ? 'selected' : '';
                optionsHtml += `<option value="${item.id}" ${isSelected}>${item.name || item.text}</option>`;
            });
            const rowHtml = `<div class="d-flex align-items-center gap-2" id="${rowId}"><span class="badge bg-secondary rounded-pill step-badge">Step</span><div class="flex-grow-1"><select class="hierarchical-select" name="${inputName}" style="width: 100%;">${optionsHtml}</select></div><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeHierarchicalRow('${rowId}')"><i class="fas fa-trash-alt"></i></button></div>`;
            listEl.insertAdjacentHTML('beforeend', rowHtml);
            setTimeout(() => {
                const newRow = document.getElementById(rowId);
                const newSelect = newRow.querySelector('select');
                updateStepBadges(listId);
                if (typeof $ !== 'undefined') {
                    $(newSelect).select2({ dropdownParent: $('body'), width: '100%', placeholder: "Select Value...", allowClear: true, dropdownCssClass: 'select2-dropdown-in-modal' });
                }
            }, 10);
        };

        window.removeHierarchicalRow = function(rowId) {
            const row = document.getElementById(rowId);
            if(row) {
                const parentId = row.parentElement.id;
                row.remove();
                updateStepBadges(parentId);
            }
        };

        const updateStepBadges = (listId) => {
            const list = document.getElementById(listId);
            if(!list) return;
            list.querySelectorAll('.step-badge').forEach((badge, index) => { badge.textContent = index + 1; });
        };

        window.initHierarchicalLists = function() {
            if(window.isHierarchicalInit) return;
            document.getElementById('hierarchicalRoleList').innerHTML = '';
            document.getElementById('hierarchicalUserList').innerHTML = '';
            const savedRoles = window.attendanceConfig.savedHierarchicalRoles || [];
            if(savedRoles.length > 0) savedRoles.forEach(id => addHierarchicalRow('role', id));
            else addHierarchicalRow('role');
            const savedUsers = window.attendanceConfig.savedHierarchicalUsers || [];
            if(savedUsers.length > 0) savedUsers.forEach(id => addHierarchicalRow('user', id));
            else addHierarchicalRow('user');
            window.isHierarchicalInit = true;
        };

        window.toggleEntitySelects = function() {
            const structure = document.getElementById('approvalStructure').value;
            const entity = document.getElementById('approverEntity').value;
            const roleDiv = document.getElementById('entityRoleDiv');
            const userDiv = document.getElementById('entityUserDiv');
            const hRoleWrapper = document.getElementById('hierarchicalRoleWrapper');
            const hUserWrapper = document.getElementById('hierarchicalUserWrapper');
            [roleDiv, userDiv, hRoleWrapper, hUserWrapper].forEach(div => { if(div) div.style.display = 'none'; });
            if (structure === 'single') {
                if (entity === 'role' && roleDiv) roleDiv.style.display = 'block';
                if (entity === 'individual' && userDiv) userDiv.style.display = 'block';
            } else if (structure === 'hierarchical') {
                if (entity === 'role' && hRoleWrapper) hRoleWrapper.style.display = 'block';
                if (entity === 'individual' && hUserWrapper) hUserWrapper.style.display = 'block';
            }
        };

        applyModalBinding('config_link_manual', 'manualEntryConfigModal', () => {
            togglePermissionFields();
            toggleApprovalLogic();
            initHierarchicalLists(); 
            toggleEntitySelects(); 
        });


        const form = document.getElementById('attendance-settings-form');
        if (form) {
            console.log(' Attendance Settings Form: Event listener attached');
            form.addEventListener('submit', (e) => {
                //console.log('🔵 Form submit event triggered');
                e.preventDefault();
                //console.log('🔵 Default prevented, calling submitAttendanceSettings');
                if (window.autoSaveTimeout) clearTimeout(window.autoSaveTimeout);
                submitAttendanceSettings(false);
            });
            form.addEventListener('change', (e) => {
                // Ignore elements that manage their own saving via onchange attribute (like toggles)
                if (e.target.hasAttribute('onchange')) {
                    return;
                }
                triggerAutoSave();
            });
        } else {
            console.error('❌ Attendance Settings Form: Form not found!');
        }
        toggleMethodSelectionMode();
    });
</script>
@endpush

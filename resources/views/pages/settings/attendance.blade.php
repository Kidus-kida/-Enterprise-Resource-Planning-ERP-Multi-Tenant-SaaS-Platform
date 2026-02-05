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
                <button type="submit" form="attendance-settings-form" class="btn btn-primary btn-sm ms-2">
                    <i class="la la-save"></i> {{ __('Save Changes') }}
                </button>
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

        // --- Location Rules ---
        $gpsRequired = $getValue('location_rules', 'require_gps', false);
        $geofencingEnabled = $getValue('location_rules', 'enable_geofencing', false);
        $locationRadius = $getValue('location_rules', 'location_radius_meters', 100);
        $allowRemote = $getValue('location_rules', 'allow_remote_work', true);

        // --- Penalties ---
        $latePenalty = $getValue('penalties', 'late_arrival_penalty_enabled', false);
        $earlyPenalty = $getValue('penalties', 'early_departure_penalty_enabled', false);
        $missingClockOutPenalty = $getValue('penalties', 'missing_clockout_penalty_enabled', false);

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
                                   {{ $singleMethodOnly ? 'checked' : '' }} onchange="toggleMethodSelectionMode()">
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
                                    id="missed_punch" :configureLink="route('admin.attendance-settings.missed-punch')" :showConfigure="$missedPunchApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missed_punch_approval_enabled" value="true" {{ $missedPunchApproval ? 'checked' : '' }} onchange="toggleConfigLink('missed_punch', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Attendance Corrections') }}" description="{{ __('HR/Manager can modify attendance') }}"
                                    id="corrections" :configureLink="route('admin.attendance-settings.corrections')" :showConfigure="$correctionApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="correction_approval_enabled" value="true" {{ $correctionApproval ? 'checked' : '' }} onchange="toggleConfigLink('corrections', this)">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Overtime Approval') }}" description="{{ __('Require approval for overtime') }}"
                                    id="overtime_approval" :configureLink="route('admin.attendance-settings.overtime-approval')" :showConfigure="$overtimeApproval">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="overtime_approval_enabled" value="true" {{ $overtimeApproval ? 'checked' : '' }} onchange="toggleConfigLink('overtime_approval', this)">
                            </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Auto-Approval') }}" description="{{ __('Automatically approve based on rules') }}"
                                    id="auto_approval" :configureLink="route('admin.attendance-settings.auto-approval')" :showConfigure="$autoApproval">
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
                                   {{ $autoClockOut ? 'checked' : '' }} onchange="toggleAutoClockOut(this)">
                        </div>
                    </x-settings.row>
                    
                    <div id="auto_clockout_config" class="{{ $autoClockOut ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Clock-Out Time') }}" indent>
                            <input type="time" name="auto_clockout_time" class="form-control form-control-sm" value="{{ $autoClockOutTime }}" style="width: 100px;">
                        </x-settings.row>
                    </div>
                    </div>
                </x-settings.section>
            </div>

            <!-- Location Rules -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-map-marker" title="{{ __('Location Rules') }}" description="{{ __('GPS and Geofencing') }}" />
                    
                    <x-settings.row label="{{ __('Require GPS') }}" description="{{ __('Mandatory for mobile punch') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="require_gps" value="true" {{ $gpsRequired ? 'checked' : '' }}>
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Geofencing') }}" description="{{ __('Restrict punch to allowed areas') }}"
                                    id="geofencing" configureLink="/config/geofences" :showConfigure="$geofencingEnabled">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="enable_geofencing" value="true" 
                                   {{ $geofencingEnabled ? 'checked' : '' }} onchange="toggleGeofencing(this); toggleConfigLink('geofencing', this)">
                        </div>
                    </x-settings.row>

                     <div id="geofencing_config" class="{{ $geofencingEnabled ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Location Radius') }}" description="{{ __('Max distance (meters)') }}" indent>
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <input type="number" name="location_radius_meters" class="form-control" value="{{ $locationRadius }}" step="10" min="10">
                                <span class="input-group-text">m</span>
                            </div>
                        </x-settings.row>
                    </div>
                    
                    <x-settings.row label="{{ __('Remote Work') }}" description="{{ __('Allow clock-in from anywhere') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="allow_remote_work" value="true" {{ $allowRemote ? 'checked' : '' }}>
                        </div>
                    </x-settings.row>
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
                            <input type="time" name="work_day_start_time" class="form-control form-control-sm" value="{{ $workDayStartTime }}" style="width: 100px;">
                            <span class="text-muted">-</span>
                            <input type="time" name="work_day_end_time" class="form-control form-control-sm" value="{{ $workDayEndTime }}" style="width: 100px;">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Minimum Hours') }}" description="{{ __('Required hours for full day') }}">
                         <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" name="minimum_work_hours" class="form-control" value="{{ $minWorkHours }}" step="0.5" min="0" max="24">
                            <span class="input-group-text">{{ __('Hrs') }}</span>
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Penalties -->
            <div class="col-md-6">
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

                    <x-settings.row label="{{ __('Missing Clock-Out') }}" description="{{ __('Penalty for forgotten clock-out') }}"
                                    id="missing_clockout_penalty" configureLink="/config/penalties#missing" :showConfigure="$missingClockOutPenalty">
                         <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missing_clockout_penalty_enabled" value="true" {{ $missingClockOutPenalty ? 'checked' : '' }} onchange="toggleConfigLink('missing_clockout_penalty', this)">
                        </div>
                    </x-settings.row>
                    </div>
                </x-settings.section>
            </div>

            <!-- Audit and Security -->
            <div class="col-md-6 d-flex">
                <x-settings.section class="settings-section h-100 mb-0 w-100">
                    <x-settings.header icon="la la-shield" title="{{ __('Audit and Security') }}" description="{{ __('Logging and compliance') }}" />

                    <x-settings.row label="{{ __('Audit Logging') }}" description="{{ __('Track all attendance changes') }}"
                                    id="audit_logging" configureLink="/audit/logs" :showConfigure="$auditLogging">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="audit_logging_enabled" value="true" 
                                   {{ $auditLogging ? 'checked' : '' }} onchange="toggleAuditLogging(this); toggleConfigLink('audit_logging', this)">
                        </div>
                    </x-settings.row>

                    @if($auditLogging)
                        <div id="audit_config" class="mb-2">
                            <x-settings.row label="{{ __('Logging Level') }}">
                                <select name="audit_level" class="form-select form-select-sm" style="width: 130px;">
                                    <option value="minimal" {{ $auditLevel === 'minimal' ? 'selected' : '' }}>{{ __('Minimal') }}</option>
                                    <option value="standard" {{ $auditLevel === 'standard' ? 'selected' : '' }}>{{ __('Standard') }}</option>
                                    <option value="detailed" {{ $auditLevel === 'detailed' ? 'selected' : '' }}>{{ __('Detailed') }}</option>
                                    <option value="forensic" {{ $auditLevel === 'forensic' ? 'selected' : '' }}>{{ __('Forensic') }}</option>
                                </select>
                            </x-settings.row>
                        </div>
                    @endif

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
            </div>

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
    </form>
@endsection

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

    function toggleGeofencing(checkbox) {
        const config = document.getElementById('geofencing_config');
        if (config) {
            if (checkbox.checked) config.classList.remove('d-none');
            else config.classList.add('d-none');
        }
    }

    function toggleShifts(checkbox) {
        const config = document.getElementById('shift_config');
        if (config) {
            if (checkbox.checked) config.classList.remove('d-none');
            else config.classList.add('d-none');
        }
    }
    
    
    function showToast(message, type = 'success') {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            // Fallback to alert if toastr is not available
            alert(message);
        }
    }

    function toggleGraceIn(checkbox) {
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

    function toggleGraceOut(checkbox) {
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

    function toggleAuditLogging(checkbox) {
        const config = document.getElementById('audit_config');
        if (config) {
            if (checkbox.checked) config.classList.remove('d-none');
            else config.classList.add('d-none');
        }
    }

    let autoSaveTimeout = null;

    function submitAttendanceSettings(silent = false) {
        const form = document.getElementById('attendance-settings-form');
        const formData = new FormData(form);
        const submitBtn = document.querySelector('button[form="attendance-settings-form"]');
        
        // Fix for checkbox arrays: if no checkboxes are checked, FormData won't have the field
        // We need to explicitly send an empty array by appending a dummy entry
        if (!formData.has('allowed_methods[]')) {
            // Append an entry that will be interpreted as an empty array by the backend
            formData.append('allowed_methods', '[]');
        }
        
        // Show generic loading or small indicator if needed
        // For now, we rely on the toast feedback
        
        fetch('{{ route('admin.attendance-settings.update') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    console.error('Validation errors:', data.errors);
                    throw new Error(data.message || 'Validation failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success && !silent) {
                showToast(data.message || '{{ __("Attendance settings updated successfully") }}', 'success');
            }
        })
        .catch(error => {
            console.error('Error saving settings:', error);
            // Even in silent mode, we might want to alert if save FAILED completely
            if (!silent) {
                showToast('{{ __("Failed to save settings. Please check console for details.") }}', 'error');
            }
        })
        .finally(() => {
            // Re-enable button if it was disabled
            if (!silent && submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="la la-save"></i> ' + '{{ __("Save Changes") }}';
            }
        });
    }

    function triggerAutoSave() {
        if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            submitAttendanceSettings(true); // Call in silent mode
        }, 800); // 800ms debounce
    }

    // Init
    document.addEventListener('DOMContentLoaded', function() {
        toggleMethodSelectionMode();

        const form = document.getElementById('attendance-settings-form');
        if (form) {
            // Standard form submit (Manual Save)
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (autoSaveTimeout) clearTimeout(autoSaveTimeout); // Cancel any pending auto-save
                submitAttendanceSettings(false); // Explicit save with feedback
            });

            // Silent Auto-save on change
            form.addEventListener('change', function(e) {
                // Don't auto-save if it's a specific button or non-input change if needed
                // But generally all changes in this form should be persisted
                triggerAutoSave();
            });
        }
    });
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
                <form action="{{ route('admin.attendance-settings.late-arrival.update') }}" method="POST">
                    @csrf
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
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
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
                <form action="{{ route('admin.attendance-settings.early-checkout.update') }}" method="POST">
                    @csrf
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
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
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
                <form action="{{ route('admin.attendance-settings.overtime.update') }}" method="POST">
                    @csrf
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
                                        <input type="number" name="overtime_rate_night" class="form-control" value="{{ $overtimeRateNight }}" step="0.01" min="1">
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
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
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
                <form action="{{ route('admin.attendance-settings.web-portal.update') }}" method="POST">
                    @csrf
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
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
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
                <form action="{{ route('admin.attendance-settings.manual-entry.update') }}" method="POST">
                    @csrf
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
                        <button type="submit" class="btn btn-primary">{{ __('Save Configuration') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Safe selector helper
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

        // --- EARLY CHECKOUT LOGIC ---
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


        // --- BINDINGS ---
        
        // Helper to open modal safely
        const openModal = (modalId, callback) => {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) {
                console.error(`Modal element ${modalId} not found`);
                return;
            }

            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap 5 is not loaded or not available globally');
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

        // Late Arrival Binding
        const lateArrivalLink = document.getElementById('config_link_late_arrival');
        if (lateArrivalLink) {
            lateArrivalLink.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('lateArrivalModal', () => {
                    toggleDeductionFields();
                    toggleAmountField();
                });
            });
        }

        // Early Checkout Binding
        const earlyCheckoutLink = document.getElementById('config_link_early_checkout');
        if (earlyCheckoutLink) {
            earlyCheckoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('earlyCheckoutModal', () => {
                    toggleEarlyDeductionFields();
                    toggleEarlyAmountField();
                });
            });
        }

        // Overtime Binding
        const overtimeLink = document.getElementById('config_link_overtime');
        if (overtimeLink) {
            overtimeLink.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('overtimeModal');
            });
        }

        // Web Portal Binding
        const webPortalLink = document.getElementById('config_link_web_portal');
        if (webPortalLink) {
            webPortalLink.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('webPortalModal');
            });
        }
        // --- MANUAL ENTRY LOGIC ---
        window.togglePermissionFields = function() {
            const mode = document.querySelector('input[name="manual_entry_permission_mode"]:checked').value;
            const rolesDiv = document.getElementById('allowedRolesDiv');
            if(rolesDiv) {
                rolesDiv.style.display = (mode === 'roles') ? 'block' : 'none';
            }
        };

        window.toggleApprovalLogic = function() {
            const policy = document.getElementById('manualEntryApprovalPolicy').value;
            const logicDiv = document.getElementById('approvalLogic');
            if(logicDiv) {
                logicDiv.style.display = (policy === 'manual_approval') ? 'block' : 'none';
            }
            if(policy === 'manual_approval') toggleEntitySelects();
        };

        // --- HIERARCHICAL LIST BUILDER ---
        window.isHierarchicalInit = false;

        window.addHierarchicalRow = function(type, selectedValue = null) {
            const listId = type === 'role' ? 'hierarchicalRoleList' : 'hierarchicalUserList';
            const listEl = document.getElementById(listId);
            const inputName = type === 'role' ? 'manual_entry_hierarchical_role_ids[]' : 'manual_entry_hierarchical_user_ids[]';
            const data = type === 'role' ? window.attendanceConfig.roles : window.attendanceConfig.users;
            
            const rowId = 'h_row_' + Date.now() + Math.floor(Math.random() * 1000);
            
            // Empty first option for placeholder support
            let optionsHtml = '<option value=""></option>';
            data.forEach(item => {
                const isSelected = selectedValue == item.id ? 'selected' : '';
                const text = item.name || item.text; 
                optionsHtml += `<option value="${item.id}" ${isSelected}>${text}</option>`;
            });

            const rowHtml = `
                <div class="d-flex align-items-center gap-2" id="${rowId}">
                    <span class="badge bg-secondary rounded-pill step-badge">Step</span>
                    <div class="flex-grow-1">
                        <select class="hierarchical-select" name="${inputName}" style="width: 100%;">
                            ${optionsHtml}
                        </select>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeHierarchicalRow('${rowId}')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;

            listEl.insertAdjacentHTML('beforeend', rowHtml);
            
            // Re-init Select2 with delay to prevent click-through issues
            setTimeout(() => {
                const newRow = document.getElementById(rowId);
                const newSelect = newRow.querySelector('select');
                
                // Update badges immediately
                updateStepBadges(listId);
                
                if (typeof $ !== 'undefined') {
                    $(newSelect).select2({ 
                        dropdownParent: $('body'),
                        width: '100%',
                        placeholder: "Select Value...",
                        allowClear: true,
                        dropdownCssClass: 'select2-dropdown-in-modal'
                    });
                }
            }, 10);
        };

        window.removeHierarchicalRow = function(rowId) {
            const row = document.getElementById(rowId);
            const parentId = row.parentElement.id;
            if(row) row.remove();
            updateStepBadges(parentId);
        };

        window.updateStepBadges = function(listId) {
            const list = document.getElementById(listId);
            const badges = list.querySelectorAll('.step-badge');
            badges.forEach((badge, index) => {
                badge.textContent = index + 1;
            });
        };

        window.initHierarchicalLists = function() {
            if(window.isHierarchicalInit) return;

            // Clear existing
            document.getElementById('hierarchicalRoleList').innerHTML = '';
            document.getElementById('hierarchicalUserList').innerHTML = '';

            // Populate Roles
            const savedRoles = window.attendanceConfig.savedHierarchicalRoles || [];
            if(savedRoles.length > 0) {
                savedRoles.forEach(id => addHierarchicalRow('role', id));
            } else {
                addHierarchicalRow('role'); // Add 1 empty row default
            }

            // Populate Users
            const savedUsers = window.attendanceConfig.savedHierarchicalUsers || [];
            if(savedUsers.length > 0) {
                savedUsers.forEach(id => addHierarchicalRow('user', id));
            } else {
                addHierarchicalRow('user'); // Add 1 empty row default
            }

            window.isHierarchicalInit = true;
        };

        window.toggleEntitySelects = function() {
            const structure = document.getElementById('approvalStructure').value;
            const entity = document.getElementById('approverEntity').value;
            
            const roleDiv = document.getElementById('entityRoleDiv');
            const userDiv = document.getElementById('entityUserDiv');
            const hRoleWrapper = document.getElementById('hierarchicalRoleWrapper');
            const hUserWrapper = document.getElementById('hierarchicalUserWrapper');
            
            // Hide all first
            if(roleDiv) roleDiv.style.display = 'none';
            if(userDiv) userDiv.style.display = 'none';
            if(hRoleWrapper) hRoleWrapper.style.display = 'none';
            if(hUserWrapper) hUserWrapper.style.display = 'none';

            if (structure === 'single') {
                if (entity === 'role' && roleDiv) roleDiv.style.display = 'block';
                if (entity === 'individual' && userDiv) userDiv.style.display = 'block';
            } else if (structure === 'hierarchical') {
                if (entity === 'role' && hRoleWrapper) hRoleWrapper.style.display = 'block';
                if (entity === 'individual' && hUserWrapper) hUserWrapper.style.display = 'block';
            }
        };

        // Manual Entry Binding
        const manualEntryLink = document.getElementById('config_link_manual');
        if (manualEntryLink) {
            manualEntryLink.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('manualEntryConfigModal', () => {
                    togglePermissionFields();
                    toggleApprovalLogic();
                    // Initialize Hierarchical Builder
                    initHierarchicalLists(); 
                    toggleEntitySelects(); 
                });
            });
        }


    });
</script>
@endpush

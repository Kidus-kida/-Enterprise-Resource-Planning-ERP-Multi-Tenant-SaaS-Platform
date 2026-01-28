@extends('pages.settings.index')

@section('page-header-section')
    <!-- Page Header -->
    <div class="breadcrumb-header">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Attendance Settings') }}</x-slot>
            <x-slot name="actions">
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
            </x-slot>
        </x-breadcrumb>
    </div>
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
                                   {{ in_array('biometric', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('biometric', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>
                    
                    <x-settings.row label="{{ __('Mobile App') }}" description="{{ __('GPS / Selfie check-in') }}"
                                    id="mobile" configureLink="/config/mobile" :showConfigure="in_array('mobile', $allowedMethods)">
                        <div class="form-check form-switch">
                            <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="mobile" 
                                   {{ in_array('mobile', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('mobile', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>
                    
                    <x-settings.row label="{{ __('Web Portal') }}" description="{{ __('Browser-based check-in') }}"
                                    id="web_portal" configureLink="/config/web-attendance" :showConfigure="in_array('web_based', $allowedMethods)">
                        <div class="form-check form-switch">
                             <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="web_based" 
                                   {{ in_array('web_based', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('web_portal', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                     <x-settings.row label="{{ __('Manual Entry') }}" description="{{ __('HR/Admin manual entry') }}"
                                     id="manual" configureLink="/config/manual" :showConfigure="in_array('manual', $allowedMethods)">
                        <div class="form-check form-switch">
                            <input class="form-check-input method-check" type="checkbox" role="switch" name="allowed_methods[]" value="manual"
                                   {{ in_array('manual', $allowedMethods) ? 'checked' : '' }} onchange="toggleConfigLink('manual', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                     <x-settings.row label="{{ __('Single Method Only') }}" description="{{ __('Restrict to one method only') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="single_method_only" name="single_method_only" value="true" 
                                   {{ $singleMethodOnly ? 'checked' : '' }} onchange="toggleMethodSelectionMode(); autoSaveSettings()">
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
                                    id="missed_punch" configureLink="/config/approvals#missed-punch" :showConfigure="$missedPunchApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missed_punch_approval_enabled" value="true" {{ $missedPunchApproval ? 'checked' : '' }} onchange="toggleConfigLink('missed_punch', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Attendance Corrections') }}" description="{{ __('HR/Manager can modify attendance') }}"
                                    id="corrections" configureLink="/config/approvals#correction" :showConfigure="$correctionApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="correction_approval_enabled" value="true" {{ $correctionApproval ? 'checked' : '' }} onchange="toggleConfigLink('corrections', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Overtime Approval') }}" description="{{ __('Require approval for overtime') }}"
                                    id="overtime_approval" configureLink="/config/approvals#overtime" :showConfigure="$overtimeApproval">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="overtime_approval_enabled" value="true" {{ $overtimeApproval ? 'checked' : '' }} onchange="toggleConfigLink('overtime_approval', this); autoSaveSettings()">
                            </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Auto-Approval') }}" description="{{ __('Automatically approve based on rules') }}"
                                    id="auto_approval" configureLink="/config/approvals#auto" :showConfigure="$autoApproval">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="auto_approval_enabled" value="true" {{ $autoApproval ? 'checked' : '' }} onchange="toggleConfigLink('auto_approval', this); autoSaveSettings()">
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
                                    id="shifts_enabled" configureLink="/shifts/templates" :showConfigure="$shiftsEnabled">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="shifts_enabled" value="true" 
                                   {{ $shiftsEnabled ? 'checked' : '' }} onchange="toggleShifts(this); toggleConfigLink('shifts_enabled', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <div id="shift_config" class="{{ $shiftsEnabled ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Shift Assignment') }}" indent>
                            <select name="shift_mode" class="form-select form-select-sm" style="width: 130px;" onchange="autoSaveSettings()">
                                <option value="mandatory" {{ $shiftMode === 'mandatory' ? 'selected' : '' }}>{{ __('Mandatory') }}</option>
                                <option value="optional" {{ $shiftMode === 'optional' ? 'selected' : '' }}>{{ __('Optional') }}</option>
                                <option value="disabled" {{ $shiftMode === 'disabled' ? 'selected' : '' }}>{{ __('Disabled') }}</option>
                            </select>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Grace Period (In)') }}" description="{{ __('Buffer before marking late') }}" indent>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="grace_in_enabled" value="true" 
                                           {{ $graceInEnabled ? 'checked' : '' }} onchange="toggleGraceIn(this); autoSaveSettings()">
                                </div>
                                <div class="input-group input-group-sm" style="width: 100px;" id="grace_in_input">
                                    <input type="number" name="grace_in_minutes" class="form-control {{ $graceInEnabled ? '' : 'text-muted' }}" 
                                           value="{{ $graceInMinutes }}" min="0" max="60" 
                                           onchange="autoSaveSettings()"
                                           {{ $graceInEnabled ? '' : 'readonly style=background-color:#e9ecef;' }}>
                                    <span class="input-group-text">min</span>
                                </div>
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Grace Period (Out)') }}" description="{{ __('Buffer before early leave') }}" indent>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="grace_out_enabled" value="true" 
                                           {{ $graceOutEnabled ? 'checked' : '' }} onchange="toggleGraceOut(this); autoSaveSettings()">
                                </div>
                                <div class="input-group input-group-sm" style="width: 100px;" id="grace_out_input">
                                    <input type="number" name="grace_out_minutes" class="form-control {{ $graceOutEnabled ? '' : 'text-muted' }}" 
                                           value="{{ $graceOutMinutes }}" min="0" max="60" 
                                           onchange="autoSaveSettings()"
                                           {{ $graceOutEnabled ? '' : 'readonly style=background-color:#e9ecef;' }}>
                                    <span class="input-group-text">min</span>
                                </div>
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Night Shift') }}" description="{{ __('Shifts crossing midnight') }}" indent
                                        id="night_shift" configureLink="/shifts/night-shift" :showConfigure="!$nightShift">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="night_shift_enabled" value="true" {{ $nightShift ? 'checked' : '' }} onchange="toggleConfigLink('night_shift', this, true); autoSaveSettings()">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Rotational Shifts') }}" description="{{ __('Auto-rotate employee shifts') }}" indent
                                        id="rotational_shift" configureLink="/shifts/rotation" :showConfigure="$rotationalShift">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="rotational_shift_enabled" value="true" {{ $rotationalShift ? 'checked' : '' }} onchange="toggleConfigLink('rotational_shift', this); autoSaveSettings()">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Flexible Hours') }}" description="{{ __('Employees choose work timing') }}" indent
                                        id="flexible_hours" configureLink="/shifts/flexible" :showConfigure="$flexibleHours">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="flexible_hours_enabled" value="true" {{ $flexibleHours ? 'checked' : '' }} onchange="toggleConfigLink('flexible_hours', this); autoSaveSettings()">
                            </div>
                        </x-settings.row>

                        <x-settings.row label="{{ __('Split Shifts') }}" description="{{ __('Shifts with breaks') }}" indent>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="split_shift_enabled" value="true" {{ $splitShift ? 'checked' : '' }} onchange="autoSaveSettings()">
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
                                    id="late_arrival" configureLink="/policies/attendance#late" :showConfigure="$lateArrival">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="late_arrival_enabled" value="true" {{ $lateArrival ? 'checked' : '' }} onchange="toggleConfigLink('late_arrival', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Early Checkout Rules') }}" description="{{ __('Penalties for leaving early') }}"
                                    id="early_checkout" configureLink="/policies/attendance#early" :showConfigure="$earlyCheckout">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="early_checkout_enabled" value="true" {{ $earlyCheckout ? 'checked' : '' }} onchange="toggleConfigLink('early_checkout', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Overtime') }}" description="{{ __('Track and compensate extra hours') }}"
                                    id="overtime" configureLink="/policies/overtime" :showConfigure="$overtime">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="overtime_enabled" value="true" {{ $overtime ? 'checked' : '' }} onchange="toggleConfigLink('overtime', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Half Day') }}" description="{{ __('Allow half-day attendance') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="half_day_enabled" value="true" {{ $halfDay ? 'checked' : '' }} onchange="autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Absence Tracking') }}" description="{{ __('Track employee absences') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="absence_enabled" value="true" {{ $absence ? 'checked' : '' }} onchange="autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Work from Home') }}" description="{{ __('Remote attendance rules') }}"
                                    id="wfh" configureLink="/policies/wfh" :showConfigure="$wfh">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="wfh_enabled" value="true" {{ $wfh ? 'checked' : '' }} onchange="toggleConfigLink('wfh', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Compensatory Off') }}" description="{{ __('Earn leave for extra work') }}"
                                    id="comp_off" configureLink="/policies/comp-off" :showConfigure="$compOff">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="comp_off_enabled" value="true" {{ $compOff ? 'checked' : '' }} onchange="toggleConfigLink('comp_off', this); autoSaveSettings()">
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
                            <input type="number" name="grace_period_minutes" class="form-control" value="{{ $gracePeriod }}" min="0" max="60" onchange="autoSaveSettings()">
                            <span class="input-group-text">{{ __('Min') }}</span>
                        </div>
                    </x-settings.row>
                    
                     <x-settings.row label="{{ __('Auto Clock-Out') }}" description="{{ __('Automatically check out at fixed time') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="auto_clockout_enabled" value="true" 
                                   {{ $autoClockOut ? 'checked' : '' }} onchange="toggleAutoClockOut(this); autoSaveSettings()">
                        </div>
                    </x-settings.row>
                    
                    <div id="auto_clockout_config" class="{{ $autoClockOut ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Clock-Out Time') }}" indent>
                            <input type="time" name="auto_clockout_time" class="form-control form-control-sm" value="{{ $autoClockOutTime }}" style="width: 100px;" onchange="autoSaveSettings()">
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
                            <input class="form-check-input" type="checkbox" role="switch" name="require_gps" value="true" {{ $gpsRequired ? 'checked' : '' }} onchange="autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Geofencing') }}" description="{{ __('Restrict punch to allowed areas') }}"
                                    id="geofencing" configureLink="/config/geofences" :showConfigure="$geofencingEnabled">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="enable_geofencing" value="true" 
                                   {{ $geofencingEnabled ? 'checked' : '' }} onchange="toggleGeofencing(this); toggleConfigLink('geofencing', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                     <div id="geofencing_config" class="{{ $geofencingEnabled ? '' : 'd-none' }}">
                        <x-settings.row label="{{ __('Location Radius') }}" description="{{ __('Max distance (meters)') }}" indent>
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <input type="number" name="location_radius_meters" class="form-control" value="{{ $locationRadius }}" step="10" min="10" onchange="autoSaveSettings()">
                                <span class="input-group-text">m</span>
                            </div>
                        </x-settings.row>
                    </div>
                    
                    <x-settings.row label="{{ __('Remote Work') }}" description="{{ __('Allow clock-in from anywhere') }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="allow_remote_work" value="true" {{ $allowRemote ? 'checked' : '' }} onchange="autoSaveSettings()">
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
                                       {{ in_array($day, $workingDays) ? 'checked' : '' }} autocomplete="off" onchange="autoSaveSettings()">
                                <label class="btn btn-outline-primary btn-sm pb-0 pt-0 d-flex align-items-center justify-content-center" 
                                       for="day_{{ $day }}" style="width: 36px; height: 32px; font-weight: 600;">
                                    {{ substr($allAndWeekendDays[$day], 0, 1) }}
                                </label>
                            @endforeach
                        </div>
                    </x-settings.row>
                    
                    <x-settings.row label="{{ __('Work Day Duration') }}" description="{{ __('Official start and end time') }}">
                        <div class="d-flex align-items-center gap-2">
                            <input type="time" name="work_day_start_time" class="form-control form-control-sm" value="{{ $workDayStartTime }}" style="width: 100px;" onchange="autoSaveSettings()">
                            <span class="text-muted">-</span>
                            <input type="time" name="work_day_end_time" class="form-control form-control-sm" value="{{ $workDayEndTime }}" style="width: 100px;" onchange="autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Minimum Hours') }}" description="{{ __('Required hours for full day') }}">
                         <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" name="minimum_work_hours" class="form-control" value="{{ $minWorkHours }}" step="0.5" min="0" max="24" onchange="autoSaveSettings()">
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
                            <input class="form-check-input" type="checkbox" role="switch" name="late_arrival_penalty_enabled" value="true" {{ $latePenalty ? 'checked' : '' }} onchange="toggleConfigLink('late_penalty', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Early Departure') }}" description="{{ __('Penalty for early clock-out') }}"
                                    id="early_penalty" configureLink="/config/penalties#early" :showConfigure="$earlyPenalty">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="early_departure_penalty_enabled" value="true" {{ $earlyPenalty ? 'checked' : '' }} onchange="toggleConfigLink('early_penalty', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Missing Clock-Out') }}" description="{{ __('Penalty for forgotten clock-out') }}"
                                    id="missing_clockout_penalty" configureLink="/config/penalties#missing" :showConfigure="$missingClockOutPenalty">
                         <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="missing_clockout_penalty_enabled" value="true" {{ $missingClockOutPenalty ? 'checked' : '' }} onchange="toggleConfigLink('missing_clockout_penalty', this); autoSaveSettings()">
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
                                   {{ $auditLogging ? 'checked' : '' }} onchange="toggleAuditLogging(this); toggleConfigLink('audit_logging', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    @if($auditLogging)
                        <div id="audit_config" class="mb-2">
                            <x-settings.row label="{{ __('Logging Level') }}">
                                <select name="audit_level" class="form-select form-select-sm" style="width: 130px;" onchange="autoSaveSettings()">
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
                            <input class="form-check-input" type="checkbox" role="switch" name="tamper_detection_enabled" value="true" {{ $tamperDetection ? 'checked' : '' }} onchange="autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Compliance Mode') }}" description="{{ __('SOC2, ISO, labor law compliance') }}"
                                    id="compliance" configureLink="/config/compliance" :showConfigure="$complianceMode">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="compliance_mode" value="true" {{ $complianceMode ? 'checked' : '' }} onchange="toggleConfigLink('compliance', this); autoSaveSettings()">
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
                            <input class="form-check-input" type="checkbox" role="switch" name="leave_integration_enabled" value="true" {{ $leaveIntegration ? 'checked' : '' }} onchange="toggleConfigLink('leave_integration', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Holiday Calendar') }}" description="{{ __('Auto-mark holidays') }}"
                                    id="holiday_integration" configureLink="/config/holidays" :showConfigure="$holidayIntegration">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="holiday_integration_enabled" value="true" {{ $holidayIntegration ? 'checked' : '' }} onchange="toggleConfigLink('holiday_integration', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Payroll Lock') }}" description="{{ __('Lock attendance for payroll') }}"
                                    id="payroll_lock" configureLink="/config/payroll" :showConfigure="$payrollLock">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="payroll_lock_enabled" value="true" {{ $payrollLock ? 'checked' : '' }} onchange="toggleConfigLink('payroll_lock', this); autoSaveSettings()">
                        </div>
                    </x-settings.row>

                    <x-settings.row label="{{ __('Payroll Export') }}" description="{{ __('Export data for payroll processing') }}"
                                    id="payroll_export" configureLink="/config/payroll#export" :showConfigure="$payrollExport">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="payroll_export_enabled" value="true" {{ $payrollExport ? 'checked' : '' }} onchange="toggleConfigLink('payroll_export', this); autoSaveSettings()">
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

    function autoSaveSettings() {
        const form = document.getElementById('attendance-settings-form');
        const formData = new FormData(form);
        
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
            if (data.success) {
                showToast(data.message || 'Settings saved successfully', 'success');
            }
        })
        .catch(error => {
            console.error('Error saving settings:', error);
            showToast('Failed to save settings. Please check console for details.', 'error');
        });
    }

    function toggleAuditLogging(checkbox) {
        const config = document.getElementById('audit_config');
        if (config) {
            if (checkbox.checked) config.classList.remove('d-none');
            else config.classList.add('d-none');
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', function() {
        toggleMethodSelectionMode();
    });
</script>
@endpush

<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttendanceSettingsController extends Controller
{
    /**
     * Display attendance settings page
     */
    public function index()
    {
        $pageTitle = __('Attendance Settings');
        $settings = AttendanceSetting::getAllByCategory();
        $roles = \Spatie\Permission\Models\Role::all();
        $users = \App\Models\User::where('is_active', true)->where('type', 'employee')->get();
        
        // Missing Punch Settings for Modal
        $missingPunchSettings = [
            'auto_detect' => filter_var(AttendanceSetting::get('missing_punch_auto_detect', true), FILTER_VALIDATE_BOOLEAN),
            'grace_period' => AttendanceSetting::get('missing_punch_grace_period', 30),
            'notification_enabled' => filter_var(AttendanceSetting::get('missing_punch_notification_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'notify_employee' => filter_var(AttendanceSetting::get('missing_punch_notify_employee', true), FILTER_VALIDATE_BOOLEAN),
            'notify_supervisor' => filter_var(AttendanceSetting::get('missing_punch_notify_supervisor', true), FILTER_VALIDATE_BOOLEAN),
            'action' => AttendanceSetting::get('missing_punch_action', 'mark_absent'),
            'allow_backdated' => filter_var(AttendanceSetting::get('missing_punch_allow_backdated', true), FILTER_VALIDATE_BOOLEAN),
            'backdate_limit_days' => AttendanceSetting::get('missing_punch_backdate_limit_days', 2),
            'require_reason' => filter_var(AttendanceSetting::get('missing_punch_require_reason', true), FILTER_VALIDATE_BOOLEAN),
            'auto_pair' => filter_var(AttendanceSetting::get('missing_punch_auto_pair', true), FILTER_VALIDATE_BOOLEAN),
            'auto_pair_threshold' => AttendanceSetting::get('missing_punch_auto_pair_threshold', 60),
            'deduction_type' => AttendanceSetting::get('missing_punch_deduction_type', 'none'),
            'deduction_amount' => AttendanceSetting::get('missing_punch_deduction_amount', 0),
            'max_occurrences' => AttendanceSetting::get('missing_punch_max_occurrences', 3),
        ];

        return view('pages.settings.attendance', compact('pageTitle', 'settings', 'roles', 'users', 'missingPunchSettings'));
    }

    /**
     * Update attendance settings
     */
    public function update(Request $request)
    {
        try {
            // Track status changes for Night Shift
            $currentNightShift = AttendanceSetting::get('night_shift_enabled', true);
            $newNightShift = $currentNightShift;
            
            if ($request->has('night_shift_enabled')) {
                $newNightShift = filter_var($request->night_shift_enabled, FILTER_VALIDATE_BOOLEAN);
            } elseif ($request->isMethod('put') || $request->isMethod('post')) {
                // If it's a batch update and the key is missing from the request, 
                // it might mean it was a checkbox that was unchecked.
                // However, we only do this if it was actually in the form.
                // In our specific Attendance Settings form, we handle everything via AJAX.
                $newNightShift = false;
            }

            $isDisablingNightShift = ($currentNightShift === true && $newNightShift === false);
            $isEnablingNightShift = ($currentNightShift === false && $newNightShift === true);

            $errors = AttendanceSetting::setMultiple($request->except('_token', '_method'));
            
            if (!empty($errors)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $errors,
                        'message' => __('Validation failed')
                    ], 422);
                }
                return back()->withErrors($errors)->withInput();
            }

            // Sync Shifts based on Night Shift toggle change
            if ($isDisablingNightShift) {
                // Deactivate all active night shifts and mark them for later reactivation
                \App\Models\Shift::where('is_active', 1)->get()->each(function($shift) {
                    if ($shift->isRestrictedNightShift()) {
                        $shift->update([
                            'is_active' => 0,
                            'deactivated_by_system' => 1
                        ]);
                    }
                });
            } elseif ($isEnablingNightShift) {
                // Reactivate shifts that were previously deactivated by the system
                \App\Models\Shift::where('deactivated_by_system', 1)->get()->each(function($shift) {
                    $shift->update([
                        'is_active' => 1,
                        'deactivated_by_system' => 0
                    ]);
                });
            }

            $message = __('Attendance settings updated successfully');
            
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            $notification = notify($message);
            return back()->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating attendance settings: ' . $e->getMessage());
            
            $errorMessage = __('Failed to update settings');
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            $notification = notify($errorMessage, 'error');
            return back()->with($notification);
        }
    }

    /**
     * Show night shift configuration
     */
    public function nightShift()
    {
        $pageTitle = __('Night Shift Configuration');
        
        $settings = [
            'night_shift_enabled' => AttendanceSetting::get('night_shift_enabled', true),
            'night_time_start' => AttendanceSetting::get('night_time_start', '22:00'),
            'night_time_end' => AttendanceSetting::get('night_time_end', '06:00'),
        ];
        
        return view('pages.settings.attendance_night_shift', compact('pageTitle', 'settings'));
    }

    /**
     * Update night shift configuration
     */
    public function updateNightShift(Request $request)
    {
        $request->validate([
            'night_time_start' => 'required|date_format:H:i',
            'night_time_end' => 'required|date_format:H:i',
        ]);

        try {
            AttendanceSetting::set('night_time_start', $request->night_time_start);
            AttendanceSetting::set('night_time_end', $request->night_time_end);
            
            $notification = notify(__('Night shift configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating night shift configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update night shift configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Update Late Arrival configuration
     */
    public function updateLateArrival(Request $request)
    {
        $request->validate([
            'late_arrival_grace_period' => 'required|integer|min:0',
            'late_arrival_penalty_type' => 'required|in:none,warning,deduction',
            'late_arrival_deduction_amount' => 'nullable|numeric|min:0',
            'late_arrival_deduction_type' => 'nullable|in:fixed,per_minute,percentage,half_day,full_day',
        ]);

        try {
            AttendanceSetting::set('late_arrival_grace_period', $request->late_arrival_grace_period);
            AttendanceSetting::set('late_arrival_penalty_type', $request->late_arrival_penalty_type);
            
            if ($request->has('late_arrival_deduction_amount')) {
                AttendanceSetting::set('late_arrival_deduction_amount', $request->late_arrival_deduction_amount);
            }
            
            if ($request->has('late_arrival_deduction_type')) {
                AttendanceSetting::set('late_arrival_deduction_type', $request->late_arrival_deduction_type);
            }
            
            $notification = notify(__('Late arrival configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating late arrival configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update late arrival configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Update Early Checkout configuration
     */
    public function updateEarlyCheckout(Request $request)
    {
        $request->validate([
            'early_checkout_grace_period' => 'required|integer|min:0',
            'early_checkout_penalty_type' => 'required|in:none,warning,deduction',
            'early_checkout_deduction_amount' => 'nullable|numeric|min:0',
            'early_checkout_deduction_type' => 'nullable|in:fixed,per_minute,percentage,half_day,full_day',
        ]);

        try {
            AttendanceSetting::set('early_checkout_grace_period', $request->early_checkout_grace_period);
            AttendanceSetting::set('early_checkout_penalty_type', $request->early_checkout_penalty_type);
            
            if ($request->has('early_checkout_deduction_amount')) {
                AttendanceSetting::set('early_checkout_deduction_amount', $request->early_checkout_deduction_amount);
            }
            
            if ($request->has('early_checkout_deduction_type')) {
                AttendanceSetting::set('early_checkout_deduction_type', $request->early_checkout_deduction_type);
            }
            
            $notification = notify(__('Early checkout configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating early checkout configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update early checkout configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Update Overtime configuration
     */
    public function updateOvertime(Request $request)
    {
        $request->validate([
            'overtime_min_minutes' => 'required|integer|min:0',
            'overtime_rate_normal' => 'required|numeric|min:1',
            'overtime_rate_night' => 'required|numeric|min:1',
            'overtime_rate_dayoff' => 'required|numeric|min:1',
            'overtime_rate_holiday' => 'required|numeric|min:1',
        ]);

        try {
            AttendanceSetting::set('overtime_min_minutes', $request->overtime_min_minutes);
            AttendanceSetting::set('overtime_rate_normal', $request->overtime_rate_normal);
            AttendanceSetting::set('overtime_rate_night', $request->overtime_rate_night);
            AttendanceSetting::set('overtime_rate_dayoff', $request->overtime_rate_dayoff);
            AttendanceSetting::set('overtime_rate_holiday', $request->overtime_rate_holiday);
            
            $notification = notify(__('Overtime configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating overtime configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update overtime configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Update Web Portal configuration
     */
    public function updateWebPortal(Request $request)
    {
        $request->validate([
            'web_portal_require_gps' => 'nullable|boolean',
            'web_portal_ip_whitelist' => 'nullable|string',
            'web_portal_allowed_hours_start' => 'nullable|date_format:H:i',
            'web_portal_allowed_hours_end' => 'nullable|date_format:H:i',
        ]);

        try {
            // Handle checkbox: if not present (unchecked), it should be false
            AttendanceSetting::set('web_portal_require_gps', $request->has('web_portal_require_gps') ? true : false);
            AttendanceSetting::set('web_portal_ip_whitelist', $request->web_portal_ip_whitelist ?? '');
            AttendanceSetting::set('web_portal_allowed_hours_start', $request->web_portal_allowed_hours_start ?? '');
            AttendanceSetting::set('web_portal_allowed_hours_end', $request->web_portal_allowed_hours_end ?? '');
            
            $notification = notify(__('Web Portal configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating web portal configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update web portal configuration'), 'error');
            return back()->withInput();
        }
    }

    public function updateManualEntry(Request $request)
    {
        $request->validate([
            'manual_entry_permission_mode' => 'required|string|in:roles,everyone',
            'manual_entry_allowed_roles' => 'nullable|array',
            'manual_entry_approval_policy' => 'required|string|in:auto_approve,manual_approval',
            'manual_entry_approval_structure' => 'required_if:manual_entry_approval_policy,manual_approval|string|in:single,hierarchical',
            'manual_entry_approver_entity' => 'required_if:manual_entry_approval_policy,manual_approval|string|in:role,individual',
            'manual_entry_approver_role_id' => 'nullable|exists:roles,id',
            'manual_entry_approver_user_id' => 'nullable|exists:users,id',
            'manual_entry_hierarchical_role_ids' => 'nullable|array',
            'manual_entry_hierarchical_user_ids' => 'nullable|array',
            'manual_entry_track_project' => 'nullable|boolean',
            'manual_entry_require_project' => 'nullable|boolean',
            'manual_entry_require_reason' => 'nullable|boolean',
            'manual_entry_max_days_back' => 'required|integer|min:0',
            'manual_entry_allow_future' => 'nullable|boolean',
        ]);

        try {
            // General Settings
            AttendanceSetting::set('manual_entry_permission_mode', $request->manual_entry_permission_mode);
            AttendanceSetting::set('manual_entry_allowed_roles', $request->manual_entry_allowed_roles ?? []);
            
            // Approval Settings
            AttendanceSetting::set('manual_entry_approval_policy', $request->manual_entry_approval_policy);
            AttendanceSetting::set('manual_entry_approval_structure', $request->manual_entry_approval_structure ?? 'single');
            AttendanceSetting::set('manual_entry_approver_entity', $request->manual_entry_approver_entity ?? 'role');
            AttendanceSetting::set('manual_entry_approver_role_id', $request->manual_entry_approver_role_id);
            AttendanceSetting::set('manual_entry_approver_user_id', $request->manual_entry_approver_user_id);
            
            // Hierarchical Arrays
            AttendanceSetting::set('manual_entry_hierarchical_role_ids', $request->manual_entry_hierarchical_role_ids ?? []);
            AttendanceSetting::set('manual_entry_hierarchical_user_ids', $request->manual_entry_hierarchical_user_ids ?? []);

            // Booleans (Checkboxes)
            AttendanceSetting::set('manual_entry_track_project', $request->has('manual_entry_track_project'));
            AttendanceSetting::set('manual_entry_require_project', $request->has('manual_entry_require_project'));
            AttendanceSetting::set('manual_entry_require_reason', $request->has('manual_entry_require_reason'));
            AttendanceSetting::set('manual_entry_allow_future', $request->has('manual_entry_allow_future'));

            // Integers
            AttendanceSetting::set('manual_entry_max_days_back', $request->manual_entry_max_days_back);

            $notification = notify(__('Manual Entry configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);

        } catch (\Exception $e) {
            \Log::error('Error updating manual entry configuration: ' . $e->getMessage());
            $notification = notify(__('Failed to update manual entry configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Reset settings to defaults
     */
    public function reset()
    {
        try {
            // Re-run seeder
            \Artisan::call('db:seed', ['--class' => 'AttendanceSettingsSeeder']);
            
            // Clear cache
            AttendanceSetting::clearCache();
            
            $notification = notify(__('Attendance settings reset to defaults'));
            return back()->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error resetting attendance settings: ' . $e->getMessage());
            
            $notification = notify(__('Failed to reset settings'), 'error');
            return back()->with($notification);
        }
    }

    /**
     * Clear settings cache
     */
    public function clearCache()
    {
        try {
            AttendanceSetting::clearCache();
            
            $notification = notify(__('Attendance settings cache cleared'));
            return back()->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error clearing cache: ' . $e->getMessage());
            
            $notification = notify(__('Failed to clear cache'), 'error');
            return back()->with($notification);
        }
    }

    /**
     * Show missed punch configuration
     */
    public function missedPunch()
    {
        $pageTitle = __('Missed Punch Configuration');
        
        $settings = [
            'missed_punch_approval_enabled' => AttendanceSetting::get('missed_punch_approval_enabled', true),
            'missed_punch_retroactive_limit' => AttendanceSetting::get('missed_punch_retroactive_limit', 7),
            'missed_punch_max_requests_per_month' => AttendanceSetting::get('missed_punch_max_requests_per_month', 5),
            'missed_punch_require_reason' => AttendanceSetting::get('missed_punch_require_reason', true),
            'missed_punch_approval_mode' => AttendanceSetting::get('missed_punch_approval_mode', 'manager'),
        ];
        
        return view('pages.settings.attendance_missed_punch', compact('pageTitle', 'settings'));
    }

    /**
     * Update missed punch configuration
     */
    public function updateMissedPunch(Request $request)
    {
        $request->validate([
            'missed_punch_retroactive_limit' => 'required|integer|min:0|max:30',
            'missed_punch_max_requests_per_month' => 'required|integer|min:1|max:31',
            'missed_punch_approval_mode' => 'required|in:manager,hr,multi',
        ]);

        try {
            AttendanceSetting::set('missed_punch_retroactive_limit', $request->missed_punch_retroactive_limit);
            AttendanceSetting::set('missed_punch_max_requests_per_month', $request->missed_punch_max_requests_per_month);
            AttendanceSetting::set('missed_punch_require_reason', $request->has('missed_punch_require_reason'));
            AttendanceSetting::set('missed_punch_approval_mode', $request->missed_punch_approval_mode);
            
            $notification = notify(__('Missed punch configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating missed punch configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update missed punch configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Show attendance corrections configuration
     */
    public function corrections()
    {
        $pageTitle = __('Attendance Corrections Configuration');
        
        $settings = [
            'correction_approval_enabled' => AttendanceSetting::get('correction_approval_enabled', true),
            'correction_retroactive_limit' => AttendanceSetting::get('correction_retroactive_limit', 30),
            'correction_require_reason' => AttendanceSetting::get('correction_require_reason', true),
            'correction_audit_trail_enabled' => AttendanceSetting::get('correction_audit_trail_enabled', true),
        ];
        
        return view('pages.settings.attendance_corrections', compact('pageTitle', 'settings'));
    }

    /**
     * Update attendance corrections configuration
     */
    public function updateCorrections(Request $request)
    {
        $request->validate([
            'correction_retroactive_limit' => 'required|integer|min:0|max:365',
        ]);

        try {
            AttendanceSetting::set('correction_retroactive_limit', $request->correction_retroactive_limit);
            AttendanceSetting::set('correction_require_reason', $request->has('correction_require_reason'));
            AttendanceSetting::set('correction_audit_trail_enabled', $request->has('correction_audit_trail_enabled'));
            
            $notification = notify(__('Attendance corrections configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating attendance corrections configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update attendance corrections configuration'), 'error');
            return back()->withInput();
        }
    }

    /**
     * Show overtime approval configuration
     */
    public function overtimeApproval()
    {
        $pageTitle = __('Overtime Approval Configuration');
        
        $settings = [
            'overtime_approval_enabled' => filter_var(AttendanceSetting::get('overtime_approval_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'request_by_employee' => filter_var(AttendanceSetting::get('ot_request_by_employee', true), FILTER_VALIDATE_BOOLEAN),
            'request_by_supervisor' => filter_var(AttendanceSetting::get('ot_request_by_supervisor', true), FILTER_VALIDATE_BOOLEAN),
            'request_by_hr' => filter_var(AttendanceSetting::get('ot_request_by_hr', false), FILTER_VALIDATE_BOOLEAN),
            'request_auto_generated' => filter_var(AttendanceSetting::get('ot_request_auto_generated', false), FILTER_VALIDATE_BOOLEAN),
            'request_timing' => AttendanceSetting::get('ot_request_timing', 'both'),
            'level1_approver' => AttendanceSetting::get('ot_level1_approver', 'supervisor'),
            'approval_mode' => AttendanceSetting::get('ot_approval_mode', 'sequential'),
            'require_punch_records' => filter_var(AttendanceSetting::get('ot_require_punch_records', true), FILTER_VALIDATE_BOOLEAN),
            'allow_manual_ot' => filter_var(AttendanceSetting::get('ot_allow_manual', false), FILTER_VALIDATE_BOOLEAN),
            'approval_hierarchy' => AttendanceSetting::get('ot_approval_hierarchy', 1),
            'level2_approver' => AttendanceSetting::get('ot_level2_approver', 'department_head'),
            'level3_approver' => AttendanceSetting::get('ot_level3_approver', 'director'),
            'notify_approver' => filter_var(AttendanceSetting::get('ot_notify_approver', true), FILTER_VALIDATE_BOOLEAN),
            'notify_employee' => filter_var(AttendanceSetting::get('ot_notify_employee', true), FILTER_VALIDATE_BOOLEAN),
            'notify_hr' => filter_var(AttendanceSetting::get('ot_notify_hr', false), FILTER_VALIDATE_BOOLEAN),
            'enable_audit_logging' => filter_var(AttendanceSetting::get('ot_enable_audit', true), FILTER_VALIDATE_BOOLEAN),
            'enable_payroll_sync' => filter_var(AttendanceSetting::get('ot_payroll_sync', false), FILTER_VALIDATE_BOOLEAN),
        ];
        
        return view('pages.settings.attendance_overtime', compact('pageTitle', 'settings'));
    }

    /**
     * Update overtime approval configuration
     */
    public function updateOvertimeApproval(Request $request)
    {
        $request->validate([
            'request_timing' => 'nullable|in:pre,post,both',
            'level1_approver' => 'nullable|in:supervisor,department_head,hr',
            'approval_mode' => 'nullable|in:sequential,parallel',
            'approval_hierarchy' => 'nullable|integer|min:1|max:3',
            'level2_approver' => 'nullable|string',
            'level3_approver' => 'nullable|string',
        ]);

        try {
            // Save main toggle
            AttendanceSetting::set('overtime_approval_enabled', $request->has('overtime_approval_enabled'));
            
            // Save request initiation settings
            AttendanceSetting::set('ot_request_by_employee', $request->has('request_by_employee'));
            AttendanceSetting::set('ot_request_by_supervisor', $request->has('request_by_supervisor'));
            AttendanceSetting::set('ot_request_by_hr', $request->has('request_by_hr'));
            AttendanceSetting::set('ot_request_auto_generated', $request->has('request_auto_generated'));
            
            // Save request timing
            AttendanceSetting::set('ot_request_timing', $request->request_timing ?? 'both');
            
            // Save workflow settings
            AttendanceSetting::set('ot_level1_approver', $request->level1_approver ?? 'supervisor');
            AttendanceSetting::set('ot_approval_mode', $request->approval_mode ?? 'sequential');
            
            // Save rules & validation
            AttendanceSetting::set('ot_require_punch_records', $request->has('require_punch_records'));
            AttendanceSetting::set('ot_allow_manual', $request->has('allow_manual_ot'));
            
            // Save hierarchy levels
            AttendanceSetting::set('ot_approval_hierarchy', $request->approval_hierarchy ?? 1);
            AttendanceSetting::set('ot_level2_approver', $request->level2_approver ?? 'department_head');
            AttendanceSetting::set('ot_level3_approver', $request->level3_approver ?? 'director');
            
            // Save notification settings
            AttendanceSetting::set('ot_notify_approver', $request->has('notify_approver'));
            AttendanceSetting::set('ot_notify_employee', $request->has('notify_employee'));
            AttendanceSetting::set('ot_notify_hr', $request->has('notify_hr'));
            
            // Save audit settings
            AttendanceSetting::set('ot_enable_audit', $request->has('enable_audit_logging'));

            // Save payroll integration settings
            AttendanceSetting::set('ot_payroll_sync', $request->has('enable_payroll_sync'));
            
            $notification = notify(__('Overtime approval configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.overtime-approval')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating overtime approval configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update overtime approval configuration'), 'error');
            return back()->withInput()->with($notification);
        }
    }

}

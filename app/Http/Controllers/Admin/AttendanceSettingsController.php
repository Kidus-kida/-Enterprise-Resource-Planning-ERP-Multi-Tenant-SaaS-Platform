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
        
        return view('pages.settings.attendance', compact('pageTitle', 'settings'));
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
     * Show flexible hours configuration
     */
    public function flexible()
    {
        $pageTitle = __('Flexible Hours Configuration');
        
        $settings = [
            'flexible_hours_enabled' => AttendanceSetting::get('flexible_hours_enabled', false),
            'flexible_daily_target_hours' => AttendanceSetting::get('flexible_daily_target_hours', 8.0),
            'flexible_core_start_time' => AttendanceSetting::get('flexible_core_start_time', '10:00'),
            'flexible_core_end_time' => AttendanceSetting::get('flexible_core_end_time', '15:00'),
            'flexible_window_start' => AttendanceSetting::get('flexible_window_start', '06:00'),
            'flexible_window_end' => AttendanceSetting::get('flexible_window_end', '22:00'),
        ];
        
        return view('pages.settings.attendance_flexible_hours', compact('pageTitle', 'settings'));
    }

    /**
     * Update flexible hours configuration
     */
    public function updateFlexible(Request $request)
    {
        $request->validate([
            'flexible_daily_target_hours' => 'required|numeric|min:0|max:24',
            'flexible_core_start_time' => 'required|date_format:H:i',
            'flexible_core_end_time' => 'required|date_format:H:i',
            'flexible_window_start' => 'required|date_format:H:i',
            'flexible_window_end' => 'required|date_format:H:i',
        ]);

        try {
            AttendanceSetting::set('flexible_daily_target_hours', $request->flexible_daily_target_hours);
            AttendanceSetting::set('flexible_core_start_time', $request->flexible_core_start_time);
            AttendanceSetting::set('flexible_core_end_time', $request->flexible_core_end_time);
            AttendanceSetting::set('flexible_window_start', $request->flexible_window_start);
            AttendanceSetting::set('flexible_window_end', $request->flexible_window_end);
            
            $notification = notify(__('Flexible hours configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.index')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating flexible hours configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update flexible hours configuration'), 'error');
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

    /**
     * Show auto-approval configuration
     */
    public function autoApproval()
    {
        $pageTitle = __('Auto-Approval Configuration');
        
        // Get allowed departments and ensure it's an array
        $allowedDepartments = AttendanceSetting::get('auto_approval_allowed_departments', []);
        if (is_string($allowedDepartments)) {
            $allowedDepartments = json_decode($allowedDepartments, true) ?? [];
        }
        if (!is_array($allowedDepartments)) {
            $allowedDepartments = [];
        }
        
        $settings = [
            'auto_approval_enabled' => filter_var(AttendanceSetting::get('auto_approval_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'max_hours_per_request' => AttendanceSetting::get('auto_approval_max_hours', 2),
            'min_advance_notice_hours' => AttendanceSetting::get('auto_approval_min_advance_notice', 24),
            'monthly_limit_per_employee' => AttendanceSetting::get('auto_approval_monthly_limit', 10),
            'require_attendance_records' => filter_var(AttendanceSetting::get('auto_approval_require_attendance', true), FILTER_VALIDATE_BOOLEAN),
            'check_budget_availability' => filter_var(AttendanceSetting::get('auto_approval_check_budget', false), FILTER_VALIDATE_BOOLEAN),
            'verify_policy_compliance' => filter_var(AttendanceSetting::get('auto_approval_verify_compliance', false), FILTER_VALIDATE_BOOLEAN),
            'fallback_action' => AttendanceSetting::get('auto_approval_fallback_action', 'level1'),
            'notify_supervisor' => filter_var(AttendanceSetting::get('auto_approval_notify_supervisor', true), FILTER_VALIDATE_BOOLEAN),
            'notify_employee' => filter_var(AttendanceSetting::get('auto_approval_notify_employee', true), FILTER_VALIDATE_BOOLEAN),
            'restrict_to_departments' => filter_var(AttendanceSetting::get('auto_approval_restrict_departments', false), FILTER_VALIDATE_BOOLEAN),
            'allowed_departments' => $allowedDepartments,
        ];
        
        return view('pages.settings.attendance_auto_approval', compact('pageTitle', 'settings'));
    }

    /**
     * Update auto-approval configuration
     */
    public function updateAutoApproval(Request $request)
    {
        $request->validate([
            'max_hours_per_request' => 'nullable|numeric|min:0|max:24',
            'min_advance_notice_hours' => 'nullable|integer|min:0|max:168',
            'monthly_limit_per_employee' => 'nullable|integer|min:0|max:100',
            'fallback_action' => 'nullable|in:level1,department_head,reject',
            'allowed_departments' => 'nullable|array',
        ]);

        try {
            // Save approval criteria
            AttendanceSetting::set('auto_approval_max_hours', $request->max_hours_per_request ?? 2);
            AttendanceSetting::set('auto_approval_min_advance_notice', $request->min_advance_notice_hours ?? 24);
            AttendanceSetting::set('auto_approval_monthly_limit', $request->monthly_limit_per_employee ?? 10);
            
            // Save additional conditions
            AttendanceSetting::set('auto_approval_require_attendance', $request->has('require_attendance_records'));
            AttendanceSetting::set('auto_approval_check_budget', $request->has('check_budget_availability'));
            AttendanceSetting::set('auto_approval_verify_compliance', $request->has('verify_policy_compliance'));
            
            // Save fallback behavior
            AttendanceSetting::set('auto_approval_fallback_action', $request->fallback_action ?? 'level1');
            
            // Save notification settings
            AttendanceSetting::set('auto_approval_notify_supervisor', $request->has('notify_supervisor'));
            AttendanceSetting::set('auto_approval_notify_employee', $request->has('notify_employee'));
            
            // Save department restrictions
            AttendanceSetting::set('auto_approval_restrict_departments', $request->has('restrict_to_departments'));
            AttendanceSetting::set('auto_approval_allowed_departments', $request->allowed_departments ?? []);
            
            $notification = notify(__('Auto-approval configuration updated successfully'));
            return redirect()->route('admin.attendance-settings.auto-approval')->with($notification);
            
        } catch (\Exception $e) {
            \Log::error('Error updating auto-approval configuration: ' . $e->getMessage());
            
            $notification = notify(__('Failed to update auto-approval configuration'), 'error');
            return back()->withInput()->with($notification);
        }
    }
}

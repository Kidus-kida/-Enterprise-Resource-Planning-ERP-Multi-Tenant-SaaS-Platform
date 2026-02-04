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
        
        return view('pages.settings.attendance', compact('pageTitle', 'settings', 'roles', 'users'));
    }

    /**
     * Update attendance settings
     */
    public function update(Request $request)
    {
        try {
            // Check if night_shift_enabled is being disabled
            $isDisablingNightShift = $request->has('night_shift_enabled') && 
                                    filter_var($request->night_shift_enabled, FILTER_VALIDATE_BOOLEAN) === false;
            
            // Also check current state if it's missing from request (missing checkbox means false)
            if (!$request->has('night_shift_enabled')) {
                $currentNightShift = AttendanceSetting::get('night_shift_enabled', true);
                if ($currentNightShift === true) {
                    $isDisablingNightShift = true;
                }
            }

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

            // If night shift was disabled, deactivate all active night shifts
            if ($isDisablingNightShift) {
                \App\Models\Shift::where('is_active', 1)->get()->each(function($shift) {
                    if ($shift->isNightShift()) {
                        $shift->update(['is_active' => 0]);
                    }
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
}

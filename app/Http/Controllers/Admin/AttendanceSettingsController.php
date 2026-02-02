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
}

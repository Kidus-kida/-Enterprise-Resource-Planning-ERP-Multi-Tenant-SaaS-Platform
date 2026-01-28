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

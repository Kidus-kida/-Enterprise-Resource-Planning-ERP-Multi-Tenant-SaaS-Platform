<?php

namespace App\Http\Controllers;

use App\Models\MissedPunchRequest;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MissedPunchRequestController extends Controller
{
    /**
     * Display a listing of the employee's missed punch requests.
     */
    public function index()
    {
        $pageTitle = __('My Missed Punch Requests');
        $requests = MissedPunchRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.employees.missed_punches.index', compact('pageTitle', 'requests'));
    }

    /**
     * Show the form for creating a new missed punch request.
     */
    public function create(Request $request)
    {
        $pageTitle = __('Submit Missed Punch Request');
        $date = $request->get('date', now()->format('Y-m-d'));
        
        // Settings for validation hints
        $retroactiveLimit = AttendanceSetting::get('missed_punch_retroactive_limit', 7);
        $requireReason = AttendanceSetting::get('missed_punch_require_reason', true);

        return view('pages.employees.missed_punches.create', compact('pageTitle', 'date', 'retroactiveLimit', 'requireReason'));
    }

    /**
     * Store a newly created missed punch request in storage.
     */
    public function store(Request $request)
    {
        $retroactiveLimit = AttendanceSetting::get('missed_punch_retroactive_limit', 7);
        $maxRequests = AttendanceSetting::get('missed_punch_max_requests_per_month', 5);
        $requireReason = AttendanceSetting::get('missed_punch_require_reason', true);

        $rules = [
            'date' => [
                'required', 
                'date', 
                'before_or_equal:today',
                function ($attribute, $value, $fail) use ($retroactiveLimit) {
                    $date = Carbon::parse($value);
                    if ($date->diffInDays(now()) > $retroactiveLimit) {
                        $fail(__('You cannot submit requests older than :days days.', ['days' => $retroactiveLimit]));
                    }
                }
            ],
            'punch_type' => 'required|in:clock_in,clock_out,both',
            'requested_start_time' => 'required_if:punch_type,clock_in,both|nullable',
            'requested_end_time' => 'required_if:punch_type,clock_out,both|nullable',
            'reason' => ($requireReason ? 'required' : 'nullable') . '|string|min:10',
        ];

        $request->validate($rules);

        // Check monthly limit
        $monthlyCount = MissedPunchRequest::where('user_id', Auth::id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($monthlyCount >= $maxRequests) {
            return back()->withErrors(['error' => __('Monthly request limit reached (:max).', ['max' => $maxRequests])])->withInput();
        }

        // Check for existing pending request for the same date
        $existing = MissedPunchRequest::where('user_id', Auth::id())
            ->where('date', $request->date)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => __('You already have a pending request for this date.')])->withInput();
        }

        // Find attendance record if exists
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('startDate', $request->date)
            ->first();

        // Prepare original data block for audit
        $originalData = null;
        if ($attendance) {
            $originalData = $attendance->load('timestamps')->toArray();
        }

        try {
            $missedPunchRequest = MissedPunchRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance ? $attendance->id : null,
                'date' => $request->date,
                'punch_type' => $request->punch_type,
                'requested_start_time' => $request->requested_start_time,
                'requested_end_time' => $request->requested_end_time,
                'reason' => $request->reason,
                'original_data' => $originalData,
            ]);

            // Notify Manager/HR based on configuration
            try {
                $mode = AttendanceSetting::get('missed_punch_approval_mode', 'manager');
                $approvers = collect();

                if ($mode === 'manager') {
                    $manager = Auth::user()->employeeDetail->manager ?? null;
                    if ($manager) {
                        $approvers->push($manager);
                    }
                }

                // Always notify an admin if no manager found or in HR mode
                if ($approvers->isEmpty() || in_array($mode, ['hr', 'multi'])) {
                    $admins = \App\Models\User::whereIn('type', [\App\Enums\UserType::SUPERADMIN, \App\Enums\UserType::ADMIN])->get();
                    $approvers = $approvers->merge($admins);
                }

                foreach ($approvers->unique('id') as $approver) {
                    $approver->notify(new \App\Notifications\NewMissedPunchRequestNotification($missedPunchRequest));
                }
            } catch (\Exception $e) {
                \Log::warning('Could not send missed punch request notification: ' . $e->getMessage());
            }

            $notification = notify(__('Missed punch request submitted successfully and is pending approval.'));
            return redirect()->route('missed-punches.index')->with($notification);

        } catch (\Exception $e) {
            \Log::error('Error submitting missed punch request: ' . $e->getMessage());
            return back()->withErrors(['error' => __('An error occurred while submitting your request.')])->withInput();
        }
    }
}

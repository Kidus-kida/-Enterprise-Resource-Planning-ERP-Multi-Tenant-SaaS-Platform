<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MissedPunchRequest;
use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MissedPunchRequestController extends Controller
{
    /**
     * Display a listing of pending missed punch requests.
     */
    public function index()
    {
        $pageTitle = __('Missed Punch Approvals');
        $user = Auth::user();
        
        $mode = \App\Models\AttendanceSetting::get('missed_punch_approval_mode', 'manager');
        $query = MissedPunchRequest::with(['user.employeeDetail.designation']);

        // If in manager mode and user is not an admin, show only their direct reports
        if ($mode === 'manager' && !$user->isSystemOwner() && $user->type !== \App\Enums\UserType::ADMIN) {
            $query->whereHas('user.employeeDetail', function($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('pages.admin.missed_punches.index', compact('pageTitle', 'requests'));
    }

    /**
     * Approve a missed punch request.
     */
    public function approve(Request $request, MissedPunchRequest $missedPunchRequest)
    {
        if ($missedPunchRequest->status !== 'pending') {
            return back()->withErrors(['error' => __('This request has already been processed.')]);
        }

        DB::beginTransaction();
        try {
            // 1. Get or Create Attendance Record
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $missedPunchRequest->user_id,
                    'startDate' => $missedPunchRequest->date->format('Y-m-d'),
                ],
                [
                    'endDate' => $missedPunchRequest->date->format('Y-m-d'),
                    'status' => 'present',
                    'company_id' => $missedPunchRequest->user->employeeDetail->company_id ?? null,
                    'created_by' => Auth::id()
                ]
            );

            // 2. Handle Timestamps based on punch type
            if ($missedPunchRequest->punch_type === 'clock_in') {
                $this->updateOrCreateTimestamp($attendance, $missedPunchRequest->requested_start_time, null);
            } elseif ($missedPunchRequest->punch_type === 'clock_out') {
                $this->updateOrCreateTimestamp($attendance, null, $missedPunchRequest->requested_end_time);
            } else {
                $this->updateOrCreateTimestamp($attendance, $missedPunchRequest->requested_start_time, $missedPunchRequest->requested_end_time);
            }

            // 3. Update Request Status
            $missedPunchRequest->update([
                'status' => 'approved',
                'approver_id' => Auth::id(),
                'approved_at' => now(),
            ]);

            // 4. Notify Employee
            try {
                $missedPunchRequest->user->notify(new \App\Notifications\MissedPunchStatusNotification($missedPunchRequest));
            } catch (\Exception $e) {
                \Log::warning('Could not send missed punch status notification: ' . $e->getMessage());
            }

            DB::commit();

            $notification = notify(__('Request approved and attendance updated successfully.'));
            return redirect()->route('admin.missed-punches.index')->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving missed punch: ' . $e->getMessage());
            return back()->withErrors(['error' => __('Failed to approve request: ') . $e->getMessage()]);
        }
    }

    /**
     * Reject a missed punch request.
     */
    public function reject(Request $request, MissedPunchRequest $missedPunchRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        if ($missedPunchRequest->status !== 'pending') {
            return back()->withErrors(['error' => __('This request has already been processed.')]);
        }

        $missedPunchRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approver_id' => Auth::id(),
        ]);

        // Notify Employee
        try {
            $missedPunchRequest->user->notify(new \App\Notifications\MissedPunchStatusNotification($missedPunchRequest));
        } catch (\Exception $e) {
            \Log::warning('Could not send missed punch status notification: ' . $e->getMessage());
        }

        $notification = notify(__('Request rejected.'));
        return redirect()->route('admin.missed-punches.index')->with($notification);
    }

    /**
     * Helper to update or create attendance timestamp.
     */
    private function updateOrCreateTimestamp(Attendance $attendance, $startTime, $endTime)
    {
        $date = is_string($attendance->startDate) ? $attendance->startDate : $attendance->startDate->format('Y-m-d');
        
        // Convert Carbon objects to time strings for safe parsing
        $startTimeStr = $startTime instanceof \Carbon\Carbon ? $startTime->toTimeString() : $startTime;
        $endTimeStr = $endTime instanceof \Carbon\Carbon ? $endTime->toTimeString() : $endTime;

        // Find existing timestamp for this day
        $timestamp = AttendanceTimestamp::where('attendance_id', $attendance->id)->first();

        if ($timestamp) {
            $updateData = [];
            if ($startTimeStr) {
                $updateData['startTime'] = Carbon::parse($date . ' ' . $startTimeStr);
            }
            if ($endTimeStr) {
                $updateData['endTime'] = Carbon::parse($date . ' ' . $endTimeStr);
            }
            $timestamp->update($updateData);
        } else {
            AttendanceTimestamp::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'company_id' => $attendance->company_id,
                'startTime' => $startTimeStr ? Carbon::parse($date . ' ' . $startTimeStr) : null,
                'endTime' => $endTimeStr ? Carbon::parse($date . ' ' . $endTimeStr) : null,
                'type' => $startTimeStr ? 'clock_in' : 'clock_out',
                'created_by' => Auth::id(),
            ]);
        }
    }
    /**
     * Remove the specified missed punch request from storage.
     */
    public function destroy(MissedPunchRequest $missedPunchRequest)
    {
        try {
            $missedPunchRequest->delete();
            $notification = notify(__('Missed punch request deleted successfully.'));
            return redirect()->route('admin.missed-punches.index')->with($notification);
        } catch (\Exception $e) {
            \Log::error('Error deleting missed punch request: ' . $e->getMessage());
            return back()->withErrors(['error' => __('Failed to delete record.')]);
        }
    }
}

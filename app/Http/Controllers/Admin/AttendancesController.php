<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserType;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\AttendanceDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class AttendancesController extends Controller
{
    
    public function index(Request $request){

        $pageTitle = __('Attendances');

        $selectedMonth = $request->month ?? Carbon::now()->month;
        $selectedYear = $request->year ?? Carbon::now()->year;

        $years_range = CarbonPeriod::create(now()->subYears(10), Carbon::now()->addYears(10))->years();
        $days_in_month = Carbon::createFromDate($selectedYear, $selectedMonth,01)->daysInMonth;
        $users = User::with(['attendances' => function ($query) use ($selectedMonth,$selectedYear) {
            $query->whereMonth('created_at', $selectedMonth)
                ->whereYear('created_at', $selectedYear)
                ->orderBy('created_at', 'desc')
                ->take(1);
        }])->where('type', UserType::EMPLOYEE);
        if(!empty($request->employee)){
            $users = $users->where('email','LIKE','%'.$request->employee.'%')
                        ->orWhere('firstname','LIKE','%'.$request->employee.'%')
                        ->orWhere('middlename','LIKE','%'.$request->employee.'%')
                        ->orWhere('lastname','LIKE','%'.$request->employee.'%')
                        ->orWhere('username','LIKE','%'.$request->employee.'%');
        }
        $employees = $users->get();
        return view('pages.attendances.index',compact(
            'pageTitle','employees','years_range','days_in_month'
        ));
    }

    public function attendanceDetails(Request $request, Attendance $attendance)
    {
        $attendanceActivity = $attendance->timestamps()->get();
        $totalHours = $attendance->timestamps()->get()->sum('totalHours');
        return view('pages.attendances.attendance-details',compact(
            'attendance','totalHours','attendanceActivity'
        ));
    }

    /**
     * My Attendance - Self-service entry for employees
     */
    public function myAttendance(Request $request)
    {
        // Check if manual entry is enabled and user has access
        $allowedMethods = AttendanceSetting::get('allowed_methods', []);
        if (!in_array('manual', $allowedMethods)) {
            abort(403, 'Manual attendance entry is not enabled.');
        }

        $permissionMode = AttendanceSetting::get('manual_entry_permission_mode', 'roles');
        if ($permissionMode !== 'everyone') {
            abort(403, 'You do not have permission to access this page.');
        }

        $pageTitle = __('My Attendance');
        $user = auth()->user();
        
        // Get configuration for UI
        $maxDaysBack = AttendanceSetting::get('manual_entry_max_days_back', 7);
        $allowFuture = AttendanceSetting::get('manual_entry_allow_future', false);
        $requireReason = AttendanceSetting::get('manual_entry_require_reason', true);
        $trackProject = AttendanceSetting::get('manual_entry_track_project', false);
        $requireProject = AttendanceSetting::get('manual_entry_require_project', false);
        
        // Load user's draft entries
        $drafts = AttendanceDraft::where('employee_id', $user->id)
            ->whereIn('status', ['draft', 'rejected'])
            ->orderBy('attendance_date', 'asc')
            ->get();
            
        // Load history (submitted, approved, rejected) - Grouped by submission time
        $history = AttendanceDraft::with('approvedBy')
            ->where('employee_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->submitted_at ? $item->submitted_at->format('Y-m-d H:i:s') : 'Unknown';
            });

        return view('pages.attendances.my-attendance', compact(
            'pageTitle', 'user', 'maxDaysBack', 'allowFuture', 'requireReason', 'trackProject', 'requireProject', 'drafts', 'history'
        ));
    }
    
    /**
     * Save a new draft entry
     */
    public function saveDraft(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'reason' => AttendanceSetting::get('manual_entry_require_reason', true) ? 'required' : 'nullable',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        
        $user = auth()->user();
        
        // Check if draft already exists for this date
        $existing = AttendanceDraft::where('employee_id', $user->id)
            ->where('attendance_date', $request->attendance_date)
            ->where('status', 'draft')
            ->first();
            
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => __('A draft entry for this date already exists. Please edit it instead.')
            ], 422);
        }
        
        $draft = AttendanceDraft::create([
            'user_id' => $user->id,
            'employee_id' => $user->id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'reason' => $request->reason,
            'project_id' => $request->project_id,
            'status' => 'draft',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => __('Draft saved successfully!'),
            'draft' => $draft
        ]);
    }
    
    /**
     * Update an existing draft
     */
    public function updateDraft(Request $request, AttendanceDraft $draft)
    {
        // Ensure user can only update their own drafts
        if ($draft->employee_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized')
            ], 403);
        }
        
        // Can only update drafts or rejected entries
        if (!in_array($draft->status, ['draft', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot edit a submitted entry')
            ], 422);
        }
        
        $request->validate([
            'attendance_date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'reason' => AttendanceSetting::get('manual_entry_require_reason', true) ? 'required' : 'nullable',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        
        $draft->update([
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'reason' => $request->reason,
            'project_id' => $request->project_id,
            'status' => 'draft', // Reset to draft if it was rejected
            'rejection_reason' => null, // Clear previous rejection reason
        ]);
        
        return response()->json([
            'success' => true,
            'message' => __('Draft updated successfully!'),
            'draft' => $draft
        ]);
    }
    
    /**
     * Delete a draft entry
     */
    public function deleteDraft(Request $request, $id)
    {
        $draft = AttendanceDraft::find($id);
        
        if (!$draft) {
             return response()->json([
                'success' => false,
                'message' => __('Entry not found')
            ], 404);
        }

        // Ensure user can only delete their own drafts
        if ($draft->employee_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized')
            ], 403);
        }
        
        // Can only delete drafts or rejected entries, not submitted/approved ones
        if (!in_array($draft->status, ['draft', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete a submitted entry')
            ], 422);
        }
        
        $draft->delete();
        
        return response()->json([
            'success' => true,
            'message' => __('Draft deleted successfully!')
        ]);
    }
    
    /**
     * Submit all draft entries for approval
     */
    public function submitDrafts(Request $request)
    {
        $user = auth()->user();
        // Fetch current drafts (including rejected ones for resubmission)
        $drafts = AttendanceDraft::where('employee_id', $user->id)
            ->whereIn('status', ['draft', 'rejected'])
            ->orderBy('attendance_date', 'asc')
            ->get();
            
        if ($drafts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No drafts to submit')
            ], 422);
        }
        
        // Update all drafts to submitted
        $timestamp = now();
        
        foreach ($drafts as $draft) {
            $draft->update([
                'status' => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => $timestamp,
                'rejection_reason' => null, // Clear any previous rejection
                'approved_by' => null, // Reset approval info
                'approved_at' => null,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => __('Successfully submitted :count entries for approval', ['count' => $drafts->count()]),
            'count' => $drafts->count()
        ]);
    }

    /**
     * Enter Attendance - For specific roles to enter attendance for team members
     */
    public function enterAttendance(Request $request)
    {
        // Check if manual entry is enabled and user has access
        $allowedMethods = AttendanceSetting::get('allowed_methods', []);
        if (!in_array('manual', $allowedMethods)) {
            abort(403, 'Manual attendance entry is not enabled.');
        }

        $permissionMode = AttendanceSetting::get('manual_entry_permission_mode', 'roles');
        if ($permissionMode !== 'roles') {
            abort(403, 'You do not have permission to access this page.');
        }

        // Check if user has one of the allowed roles
        $allowedRoles = AttendanceSetting::get('manual_entry_allowed_roles', []);
        $userRoleIds = auth()->user()->roles->pluck('id')->toArray();
        $hasRole = !empty(array_intersect($userRoleIds, $allowedRoles));
        
        if (!$hasRole) {
            abort(403, 'Your role does not have permission to enter attendance.');
        }

        $pageTitle = __('Enter Attendance');
        $employees = User::where('type', UserType::EMPLOYEE)->get();
        
        // Get configuration for UI
        $maxDaysBack = AttendanceSetting::get('manual_entry_max_days_back', 7);
        $allowFuture = AttendanceSetting::get('manual_entry_allow_future', false);
        $requireReason = AttendanceSetting::get('manual_entry_require_reason', true);
        $trackProject = AttendanceSetting::get('manual_entry_track_project', false);
        $requireProject = AttendanceSetting::get('manual_entry_require_project', false);

        // Fetch drafts created by this user (Manager), grouped by Employee
        $drafts = AttendanceDraft::with('employee')
            ->where('submitted_by', auth()->id())
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('employee_id');

        // Fetch history of submissions by this user
        $history = AttendanceDraft::with('employee', 'approvedBy')
            ->where('submitted_by', auth()->id())
            ->where('status', '!=', 'draft')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->submitted_at ? $item->submitted_at->format('Y-m-d H:i:s') : 'Unknown';
            });

        return view('pages.attendances.enter-attendance', compact(
            'pageTitle', 'employees', 'maxDaysBack', 'allowFuture', 'requireReason', 'trackProject', 'requireProject', 'drafts', 'history'
        ));
    }

    /**
     * Store Manual Attendance - Save the manually entered attendance
     */
    public function storeManualAttendance(Request $request)
    {
        // 1. Permission Check
        $allowedMethods = AttendanceSetting::get('allowed_methods', []);
        if (!in_array('manual', $allowedMethods)) {
            abort(403, 'Manual attendance entry is not enabled.');
        }

        $permissionMode = AttendanceSetting::get('manual_entry_permission_mode', 'roles');
        if ($permissionMode !== 'roles') {
            abort(403, 'You do not have permission.');
        }

        $allowedRoles = AttendanceSetting::get('manual_entry_allowed_roles', []);
        $userRoleIds = auth()->user()->roles->pluck('id')->toArray();
        if (empty(array_intersect($userRoleIds, $allowedRoles))) {
            abort(403, 'Your role does not have permission.');
        }

        // 2. Validation
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'nullable',
            'reason' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'action' => 'nullable|in:draft,submit',
        ]);

        $status = $request->action === 'submit' ? 'submitted' : 'draft';

        // 3. Create Draft
        AttendanceDraft::create([
            'user_id' => $request->employee_id, // The employee
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->attendance_date . ' ' . $request->time_in,
            'time_out' => $request->time_out ? $request->attendance_date . ' ' . $request->time_out : null,
            'reason' => $request->reason,
            'project_id' => $request->project_id,
            'status' => $status,
            'submitted_by' => auth()->id(), // The manager who entered/created it
            'submitted_at' => $status === 'submitted' ? now() : null,
        ]);

        $message = $status === 'submitted' ? 'Attendance submitted successfully.' : 'Attendance draft saved.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Submit Manual Drafts - Batch submit drafts created by manager
     */
    public function submitManualDrafts(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendance_drafts,id'
        ]);

        // Verify these drafts are created by this user
        $updated = AttendanceDraft::whereIn('id', $request->ids)
            ->where('submitted_by', auth()->id())
            ->where('status', 'draft')
            ->update([
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' drafts submitted successfully.'
        ]);
    }

    /**
     * Attendance Approvals - For approvers to review pending manual entries
     */
    public function attendanceApprovals(Request $request)
    {
        // Check if manual entry is enabled and approval is required
        $allowedMethods = AttendanceSetting::get('allowed_methods', []);
        if (!in_array('manual', $allowedMethods)) {
            abort(403, 'Manual attendance entry is not enabled.');
        }

        $approvalPolicy = AttendanceSetting::get('manual_entry_approval_policy', 'auto_approve');
        if ($approvalPolicy !== 'manual_approval') {
            abort(403, 'Attendance approval is not required.');
        }

        // Check if user is an approver
        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        $approverEntity = AttendanceSetting::get('manual_entry_approver_entity', 'role');
        $approvalStructure = AttendanceSetting::get('manual_entry_approval_structure', 'single');
        
        $isApprover = false;
        
        if ($approvalStructure === 'single') {
            if ($approverEntity === 'role') {
                $approverRoleId = AttendanceSetting::get('manual_entry_approver_role_id');
                $isApprover = in_array($approverRoleId, $userRoleIds);
            } else {
                $approverUserId = AttendanceSetting::get('manual_entry_approver_user_id');
                $isApprover = $user->id == $approverUserId;
            }
        } else {
            if ($approverEntity === 'role') {
                $hierarchicalRoleIds = AttendanceSetting::get('manual_entry_hierarchical_role_ids', []);
                $isApprover = !empty(array_intersect($userRoleIds, $hierarchicalRoleIds));
            } else {
                $hierarchicalUserIds = AttendanceSetting::get('manual_entry_hierarchical_user_ids', []);
                $isApprover = in_array($user->id, $hierarchicalUserIds);
            }
        }
        
        if (!$isApprover) {
            abort(403, 'You are not designated as an attendance approver.');
        }

        $pageTitle = __('Attendance Approvals');
        
        // Fetch all submitted drafts - Grouped by employee and submission time
        $approvals = AttendanceDraft::with('employee')
            ->whereIn('status', ['submitted', 'approved', 'rejected'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                // Group by User ID and Submission Timestamp (to separate different batches)
                return $item->employee_id . '|' . ($item->submitted_at ? $item->submitted_at->format('Y-m-d H:i:s') : '0');
            });

        return view('pages.attendances.approvals', compact(
            'pageTitle', 'approvals'
        ));
    }
    
    public function approveDraft(AttendanceDraft $draft)
    {
        // Update draft status
        $draft->update([
            'status' => 'approved',
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
        ]);
        
        // Create actual attendance record
        // Assuming Attendance model exists and matches data structure
        try {
            \App\Models\Attendance::create([
                'user_id' => $draft->employee_id,
                'employee_id' => $draft->employee_id,
                'date' => $draft->attendance_date,
                'time_in' => $draft->time_in,
                'time_out' => $draft->time_out,
                'status' => 'Present', // Or Determine based on time
                'manual_entry' => true,
            ]);
        } catch (\Exception $e) {
            // Log error but continue since draft is approved
            \Log::error('Failed to create attendance from draft: ' . $e->getMessage());
        }
        
        return response()->json(['success' => true, 'message' => __('Attendance approved successfully')]);
    }
    
    public function rejectDraft(Request $request, AttendanceDraft $draft)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        $draft->update([
            'status' => 'rejected',
            'approved_by' => \Illuminate\Support\Facades\Auth::id(), // Rejected by
            'approved_at' => now(), // Rejected at
            'rejection_reason' => $request->reason,
        ]);
        
        return response()->json(['success' => true, 'message' => __('Attendance rejected successfully')]);
    }

    public function approveBatch(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        
        $count = 0;
        foreach ($request->ids as $id) {
            $draft = AttendanceDraft::find($id);
            if ($draft && $draft->status == 'submitted') {
                $this->approveDraft($draft);
                $count++;
            }
        }
        
        return response()->json(['success' => true, 'message' => __(':count entries approved successfully', ['count' => $count])]);
    }

    public function rejectBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'reason' => 'required|string|max:255'
        ]);
        
        $count = 0;
        AttendanceDraft::whereIn('id', $request->ids)
            ->where('status', 'submitted')
            ->update([
                'status' => 'rejected',
                'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->reason
            ]);
            
        return response()->json(['success' => true, 'message' => __('Entries rejected successfully')]);
    }
}

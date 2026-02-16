<?php

namespace App\Http\Controllers;

use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Auth;
use App\DataTables\LeaveRequestDataTable;
use App\DataTables\myLeaveDataTable;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Schema;
class LeaveRequestController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(LeaveRequestDataTable $dataTable)
    {
        $pageTitle = __("Leave Request");

        return $dataTable->render('pages.leaveRequest.index', compact(
            'pageTitle',

        ));
    }
    public function myLeaveRequests(myLeaveDataTable $dataTable)
    {
        $pageTitle = __("Leave Request");

        return $dataTable->render('pages.leaveRequest.myleave', compact(
            'pageTitle',

        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leaveTypeConnection = (new LeaveType())->getConnectionName() ?? config('database.default');
        $leaveTypeQuery = LeaveType::query();

        if (Schema::connection($leaveTypeConnection)->hasColumn('leave_types', 'status')) {
            $leaveTypeQuery->where('status', 'allowed');
        }

        $leavetypes = $leaveTypeQuery->get();

        // Fetch allocations grouped by leave type
        $rawAllocations = LeaveAllocation::where('user_id', Auth::id())
            ->where('available_days', '>', 0)
            ->with('leaveType')
            ->get();

        $allocations = [];
        foreach ($rawAllocations as $alloc) {
            $name = $alloc->leaveType->type_name ?? 'Unknown';
            if (!isset($allocations[$name])) $allocations[$name] = 0;
            $allocations[$name] += $alloc->available_days;
        }

        // Pass explicit null for legacy balance variable to avoid view errors
        $balance = null;

        if (request()->ajax()) {
            return view('pages.leaveRequest.create_modal', compact('leavetypes', 'balance', 'allocations'));
        }

        return view('pages.leaveRequest.create', compact('leavetypes', 'balance', 'allocations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'leave_start_date' => ['required', 'date'],
            'leave_end_date' => ['nullable', 'date', 'after_or_equal:leave_start_date'],
            'half_day' => ['required'],
            'half_day_time' => ['nullable'],
            'request_reason' => ['nullable', 'string'],
            'attachements.*' => ['nullable', 'file', 'max:2048'],
        ]);

        $leaveService = app(LeaveService::class);
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        
        $isHalfDay = (bool) $validated['half_day'];
        $leaveEndDate = $isHalfDay
            ? $validated['leave_start_date']
            : ($validated['leave_end_date'] ?? $validated['leave_start_date']);

        // Calculate Duration
        $days = $leaveService->calculateDuration($validated['leave_start_date'], $leaveEndDate);
        if ($isHalfDay) $days = 0.5;

        if ($days <= 0) {
             return back()->with('error', __('Selected period contains only holidays or weekends (0 days).'));
        }

        // Validate Balance
        if (!$leaveService->checkBalance(Auth::id(), $leaveType->id, $days)) {
             return back()->with('error', __('Insufficient leave balance. You requested ' . $days . ' working days but have less available in your allocation.'));
        }

        // Handle File Uploads
        $paths = [];
        if ($request->hasFile('attachements')) {
            foreach ($request->file('attachements') as $file) {
                $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/leave_attachments'), $name);
                $paths[] = $name;
            }
        }

        // Determine Initial Status based on Workflow
        $status = 'pending'; // Default: Pending Manager Approval
        $currentLevel = 1;
        $requiredLevels = $leaveType->approval_levels ?? 1;

        if (!$leaveType->requires_approval) {
             $status = 'approved';
             $currentLevel = $requiredLevels;
        }

        $leaveRequest = null;
        DB::transaction(function() use (&$leaveRequest, $status, $validated, $leaveEndDate, $paths, $isHalfDay, $days, $leaveService, $leaveType, $currentLevel, $requiredLevels) {
            $leaveRequest = LeaveRequest::create([
                'employee_id' => Auth::id(),
                'leave_type_id' => $validated['leave_type_id'],
                'leave_start_date' => $validated['leave_start_date'],
                'leave_end_date' => $leaveEndDate,
                'request_reason' => html_entity_decode(strip_tags($validated['request_reason'] ?? '')),
                'attachements' => $paths,
                'half_day' => $validated['half_day_time'],
            'multiple_day' => $isHalfDay ? 0 : 1,
            'request_type' => $isHalfDay ? 'half_day' : 'full_day',
            'status' => $status,
            'current_approval_level' => $currentLevel,
            'required_approval_levels' => $requiredLevels,
                'total_days' => $days,
            ]);

            // If Auto-Approved, deduct balance immediately
            if ($status === 'approved') {
                $leaveService->deductBalance(Auth::id(), $leaveType->id, $days);
            }
        });

        $msg = $status === 'approved'
            ? __('Leave request auto-approved and balance deducted.')
            : __('Leave request submitted and is pending approval.');

        return back()->with('success', $msg);
    }

    public function show(LeaveRequest $leaverequest)
    {
        return view('pages.leaveRequest.show', compact('leaverequest'));
    }

    public function edit(LeaveRequest $leaveRequest, $id)
    {
        $leaverequest = LeaveRequest::findOrFail($id);
        return view('pages.leaveRequest.edit', compact('leaverequest'));
    }

    public function update(Request $request, $id, $employee_id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => 'nullable|string',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status === 'approved') {
            return back()->with(notify(__('Request is already approved.')));
        }

        DB::transaction(function () use ($leaveRequest, $validated, $employee_id) {

            if ($validated['status'] === 'rejected') {
                // Determine who rejected
                $rejectorRole = (Auth::user()->can('hr_access')) ? 'HR' : 'Manager'; // Simplistic role check

                $leaveRequest->update([
                    'attended_by' => Auth::id(),
                    'reject_reason' => html_entity_decode(strip_tags($validated['reject_reason'] ?? '')),
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'admin_notes' => "Rejected by $rejectorRole at level {$leaveRequest->current_approval_level}",
                ]);
                return;
            }

            // Approval Logic
            if ($validated['status'] === 'approved') {
                $requiredLevels = $leaveRequest->required_approval_levels;
                $currentLevel = $leaveRequest->current_approval_level;

                // If multi-level approval is required and we aren't at the last level
                if ($requiredLevels > 1 && $currentLevel < $requiredLevels) {

                    // Advance to next level (e.g., Manager -> HR)
                    $leaveRequest->update([
                        'current_approval_level' => $currentLevel + 1,
                        'status' => 'pending', // Keeps it pending for next approver
                        'admin_notes' => $leaveRequest->admin_notes . "\nLevel $currentLevel approved by " . Auth::user()->name,
                    ]);

                    // TODO: Notify next approver (HR) here

                } else {
                    // Final Approval
                    $leaveService = app(LeaveService::class);
                    $days = $leaveRequest->total_days; // Use cached value or recalculate

                    if (!$leaveService->checkBalance($employee_id, $leaveRequest->leave_type_id, $days)) {
                         throw ValidationException::withMessages([
                            'status' => ['Insufficient leave allocation balance for this employee.'],
                        ]);
                    }

                    $leaveService->deductBalance($employee_id, $leaveRequest->leave_type_id, $days);

                    $leaveRequest->update([
                        'status' => 'approved',
                        'attended_by' => Auth::id(),
                        'approved_at' => now(),
                        'admin_notes' => $leaveRequest->admin_notes . "\nFinal approval by " . Auth::user()->name,
                    ]);
                }
            }
        });

        $msg = $validated['status'] === 'approved' && $leaveRequest->status === 'pending'
            ? __('Request approved (moved to next approval stage).')
            : __('Leave Request has been updated');

        return back()->with(notify($msg));
    }

}

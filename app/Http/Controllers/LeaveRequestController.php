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
        $leavetypes = LeaveType::where('status', 'allowed')->get();

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
        $isHalfDay = (bool) $validated['half_day'];
        $leaveEndDate = $isHalfDay
            ? $validated['leave_start_date']
            : ($validated['leave_end_date'] ?? $validated['leave_start_date']);

        // Calculate Duration using Service (Excludes Holidays/Weekends)
        $days = $leaveService->calculateDuration($validated['leave_start_date'], $leaveEndDate);
        if ($isHalfDay) $days = 0.5;

        if ($days <= 0) {
             return back()->with('error', __('Selected period contains only holidays or weekends (0 days).'));
        }

        // Validate Balance
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
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
        
        // Determine Status based on Leave Type Configuration
        $status = 'pending';
        // If leave type doesn't require approval, auto-approve
        if (!$leaveType->requires_approval) {
             $status = 'approved';
        }

        $leaveRequest = null;
        DB::transaction(function() use (&$leaveRequest, $status, $validated, $leaveEndDate, $paths, $isHalfDay, $days, $leaveService, $leaveType) {
            $leaveRequest = LeaveRequest::create([
                'employee_id' => Auth::id(),
                'leave_type_id' => $validated['leave_type_id'],
                'leave_start_date' => $validated['leave_start_date'],
                'leave_end_date' => $leaveEndDate,
                'request_reason' => html_entity_decode(strip_tags($validated['request_reason'] ?? '')),
                'attachements' => $paths,
                'half_day' => $validated['half_day_time'],
                'multiple_day' => $isHalfDay ? 0 : 1,
                'status' => $status,
            ]);

            // If Auto-Approved, deduct balance immediately
            if ($status === 'approved') {
                $leaveService->deductBalance(Auth::id(), $leaveType->id, $days);
            }
        });

        $msg = $status === 'approved' 
            ? __('Leave request auto-approved and balance deducted.') 
            : __('Leave request submitted and is pending approval.');
            
        return back()->with(notify($msg));
    }

    /**
     * Display the specified resource.
     */


    public function show(LeaveRequest $leaverequest)
    {
        return view('pages.leaveRequest.show', compact(
            'leaverequest'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest, $id)
    {
        // dd($id);
        $leaverequest = LeaveRequest::findOrFail($id);
        return view('pages.leaveRequest.edit', compact(
            'leaverequest'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    // ← spelling kept to match your table


    public function update(Request $request, $id, $employee_id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => 'nullable|string',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);
        
        // Prevent re-approving
        if ($leaveRequest->status === 'approved') {
            return back()->with(notify(__('Request is already approved.')));
        }

        DB::transaction(function () use ($leaveRequest, $validated, $employee_id) {
            $leaveRequest->update([
                'attended_by' => Auth::id(),
                'reject_reason' => html_entity_decode(strip_tags($validated['reject_reason'] ?? '')),
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === 'approved') {
                $leaveService = app(LeaveService::class);
                
                // Recalculate duration
                $days = $leaveService->calculateDuration($leaveRequest->leave_start_date, $leaveRequest->leave_end_date);
                if ($leaveRequest->half_day) $days = 0.5;

                // Check Balance again (safety)
                if (!$leaveService->checkBalance($employee_id, $leaveRequest->leave_type_id, $days)) {
                     throw ValidationException::withMessages([
                        'status' => ['Insufficient leave allocation balance for this employee.'],
                    ]);
                }

                // Deduct
                $leaveService->deductBalance($employee_id, $leaveRequest->leave_type_id, $days);
            }
        });

        return back()->with(notify(__('Leave Request has been updated')));
    }

    public function destroy(LeaveRequest $leaveRequest,$id)
    {
        // dd( $id);
        
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->delete();
        $notification = notify(__('LeaveRequest has been deleted'));
        return back()->with($notification);
    }

}

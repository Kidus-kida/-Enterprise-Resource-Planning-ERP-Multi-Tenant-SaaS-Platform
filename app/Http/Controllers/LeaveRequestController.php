<?php

namespace App\Http\Controllers;

use App\Models\AnunalLeave;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Auth;
use App\DataTables\LeaveRequestDataTable;
use App\DataTables\myLeaveDataTable;

use Carbon\Carbon;         // spel
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
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

        // fetch the single balance row (or null if none exists)
        $balance = AnunalLeave::where('employee_id', Auth::id())->first();

        // dd($balance);
        return view('pages.leaveRequest.create', compact('leavetypes', 'balance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'leave_start_date' => ['required', 'date'],
            // leave_end_date is required only when NOT half‑day
            'leave_end_date' => [
                'nullable',
                'date',
                'after_or_equal:leave_start_date',
                // Rule::requiredIf(!$request->boolean('half_day')),
            ],
            'half_day' => ['required'],
            'half_day_time' => ['nullable'],
            'request_reason' => ['nullable', 'string'],
            'attachements.*' => ['nullable', 'file', 'max:2048'],  // 2 MB each
        ]);
        $isHalfDay = (bool) $validated['half_day'];
        // final end‑date
        $leaveEndDate = $isHalfDay
            ? $validated['leave_start_date']
            : ($validated['leave_end_date'] ?? $validated['leave_start_date']);

        $halfDayField = $validated['half_day_time'];      // morning / afternoon
        $multipleDay = $isHalfDay ? 0 : 1;
        $description = html_entity_decode(strip_tags($validated['request_reason'] ?? ''));
        $paths = [];
        if ($request->hasFile('attachements')) {
            foreach ($request->file('attachements') as $file) {
                // 1 Generate unique name 
                $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/leave_attachments'), $name);
                $paths[] = $name;
            }
        }
        LeaveRequest::create([
            'employee_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'leave_start_date' => $validated['leave_start_date'],
            'leave_end_date' => $leaveEndDate,
            'request_reason' => $description,

            'attachements' => $paths,
            'half_day' => $halfDayField,
            'multiple_day' => $multipleDay,
            'status' => 'pending',
        ]);

        $notification = notify(__('Leave request submitted and is pending approval.'));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        //
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

        DB::transaction(function () use ($leaveRequest, $validated, $employee_id) {

            $leaveRequest->update([
                'attended_by' => Auth::id(),
                'reject_reason' => html_entity_decode(strip_tags($validated['reject_reason'] ?? '')),
                'status' => $validated['status'],
            ]);
            if ($validated['status'] === 'approved') {

                $balance = AnunalLeave::where('employee_id', $employee_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /* ---- Calculate number of leave days (inclusive) ---- */
                $days = Carbon::parse($leaveRequest->leave_start_date)
                    ->diffInDays(Carbon::parse($leaveRequest->leave_end_date)) + 1;

                /* ---- Safeguard: enough combined balance? ---- */
                $totalAvailable = $balance->previous_year + $balance->current_year; // or use $balance->total_anunal_leave
                if ($days > $totalAvailable) {
                    throw ValidationException::withMessages([
                        'status' => ['Not enough annual‑leave balance.'],
                    ]);
                }
                $usePrev = min($days, $balance->previous_year);
                $useCurr = $days - $usePrev;

                if ($usePrev > 0) {
                    $balance->decrement('previous_year', $usePrev);
                }
                if ($useCurr > 0) {
                    $balance->decrement('current_year', $useCurr);
                }
                $balance->decrement('total_anunal_leave', $days);
            }
        });

        return back()->with(notify(__('Leave Request has been updated')));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        //
    }
}

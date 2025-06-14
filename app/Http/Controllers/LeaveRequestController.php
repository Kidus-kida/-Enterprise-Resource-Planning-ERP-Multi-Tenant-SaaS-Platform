<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Auth;
use App\DataTables\LeaveRequestDataTable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
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
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leavetypes = LeaveType::where('status', '=', 'allowed')->get();
        return view('pages.leaveRequest.create', compact('leavetypes'));
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


    public function update(Request $request, $id)   // $ticket is just the ID
    {
        $validated = $request->validate([
            'status' => 'required',
            'reject_reason' => 'nullable',
        ]);

        $leaverequest = LeaveRequest::findOrFail($id);          // 🔍 convert to model

        $description = html_entity_decode(strip_tags($validated['reject_reason'] ?? ''));
        $leaverequest->update([
            'attended_by' => Auth::id(),
            'reject_reason' => $description,
            'status' => $request->status,
        ]);

        $notification = notify(__('Leave Request has been updated'));
        return back()->with($notification);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        //
    }
}

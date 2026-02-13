<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\LeaveAccrualPlan;
use Illuminate\Http\Request;

use App\DataTables\leaveTypeDataTable;
class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(leaveTypeDataTable $dataTable)
    {
        $pageTitle = __("LeaveTypes");
        return $dataTable->render('pages.leavetype.index', compact(
            'pageTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accrualPlans = LeaveAccrualPlan::where('is_active', true)->get();
        return view('pages.leavetype.create', compact('accrualPlans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_name' => 'required',
            'max_date_allowed' => 'required',
            'leave_allowed_interval' => 'nullable',
            'description' => 'nullable',
        ]);

        $description = $request->description ? html_entity_decode(strip_tags($request->description)) : null;

        $leavetype = LeaveType::create([
            'type_name' => $request->type_name,
            'max_date_allowed' => $request->max_date_allowed,
            'leave_allowed_interval' => $request->leave_allowed_interval,
            'default_accrual_plan_id' => $request->default_accrual_plan_id,
            'description' => $description,
            'status' => 'allowed',
        ]);

        $notification = notify(__('Leave Type has been Created'));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        $accrualPlans = LeaveAccrualPlan::where('is_active', true)->get();
        return view('pages.leavetype.edit', compact('leaveType', 'accrualPlans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $request->validate([
            'type_name' => 'required',
            'max_date_allowed' => 'required',
        ]);

        $leaveType->update([
            'type_name' => $request->type_name,
            'max_date_allowed' => $request->max_date_allowed,
            'leave_allowed_interval' => $request->leave_allowed_interval,
            'default_accrual_plan_id' => $request->default_accrual_plan_id,
            'description' => $request->description ? html_entity_decode(strip_tags($request->description)) : $leaveType->description,
        ]);

        $notification = notify(__('Leave Type has been Updated'));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        //
    }
}

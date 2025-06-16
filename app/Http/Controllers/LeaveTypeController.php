<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

use App\DataTables\LeaveTypeDataTable;
class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LeaveTypeDataTable $dataTable)
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
        //  $users = User::where('type', UserType::EMPLOYEE)->whereIsActive(true)->get();
        return view('pages.leavetype.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(rules: [
            'type_name' => 'required',
            'max_date_allowed' => 'required',
            'leave_allowed_interval' => 'nullable',
            'description' => 'nullable',
        ]);
        // dd($request);
        $leavetype = LeaveType::create([
            'type_name' => $request->type_name,
            'max_date_allowed' => $request->max_date_allowed,
            'leave_allowed_interval' => $request->leave_allowed_interval,
            $description = strip_tags($request->description), // remove HTML tags like <h1></h1>  <p></p>
            $description = html_entity_decode($description), // convert &nbsp; to normal spaces
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        //
    }
}

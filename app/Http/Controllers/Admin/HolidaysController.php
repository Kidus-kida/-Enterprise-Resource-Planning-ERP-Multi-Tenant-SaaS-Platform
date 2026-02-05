<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\HolidayDataTable;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(HolidayDataTable $dataTable)
    {
        $pageTitle = __("Holidays");
        return $dataTable->render('pages.holidays.index',compact(
            'pageTitle',
        ));
    }

    public function calendar(){
        $pageTitle = __("Holidays Calendar");
        $events = Holiday::get()->map(function(Holiday $holiday){
            return [
                'title' => $holiday->name,
                'start' => $holiday->startDate,
                'end' => $holiday->endDate,
                'className' => 'bg-'.!empty($holiday->color) ? $holiday->color->value: 'primary',
            ];
        });
        return view('pages.holidays.calendar',compact(
            'pageTitle','events'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.holidays.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'description' => 'nullable|max:255',
            'duration' => 'required|in:full_day,half_day',
            'exclude_from_leave' => 'boolean',
            'weekend_adjustment' => 'required|in:none,next_monday,previous_friday',
            'is_paid' => 'boolean',
            'block_leave_requests' => 'boolean',
            'allow_attendance_exception' => 'boolean',
        ]);
        
        Holiday::create([
            'name' => $request->name,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'description' => $request->description,
            'is_annual' => !empty($request->is_annual) ? true : false,
            'color' => $request->color,
            // New Odoo fields
            'duration' => $request->duration ?? 'full_day',
            'applicable_to' => $request->applicable_to ?? ['type' => 'all'],
            'exclude_from_leave' => $request->has('exclude_from_leave') ? (bool)$request->exclude_from_leave : true,
            'weekend_adjustment' => $request->weekend_adjustment ?? 'none',
            'is_paid' => $request->has('is_paid') ? (bool)$request->is_paid : true,
            'block_leave_requests' => $request->has('block_leave_requests') ? (bool)$request->block_leave_requests : false,
            'allow_attendance_exception' => $request->has('allow_attendance_exception') ? (bool)$request->allow_attendance_exception : false,
        ]);
        
        $notification = notify(__("Holiday has been created"));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $public_holiday)
    {
        $holiday = $public_holiday;
        return view('pages.holidays.edit',compact(
            'holiday'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $public_holiday)
    {
        $holiday = $public_holiday;
        $request->validate([
            'name' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'description' => 'nullable|max:255',
            'duration' => 'required|in:full_day,half_day',
            'exclude_from_leave' => 'boolean',
            'weekend_adjustment' => 'required|in:none,next_monday,previous_friday',
            'is_paid' => 'boolean',
            'block_leave_requests' => 'boolean',
            'allow_attendance_exception' => 'boolean',
        ]);
        
        $holiday->update([
            'name' => $request->name,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
            'description' => $request->description,
            'is_annual' => !empty($request->is_annual) ? true : false,
            'color' => $request->color,
            // New Odoo fields
            'duration' => $request->duration ?? 'full_day',
            'applicable_to' => $request->applicable_to ?? ['type' => 'all'],
            'exclude_from_leave' => $request->has('exclude_from_leave') ? (bool)$request->exclude_from_leave : true,
            'weekend_adjustment' => $request->weekend_adjustment ?? 'none',
            'is_paid' => $request->has('is_paid') ? (bool)$request->is_paid : true,
            'block_leave_requests' => $request->has('block_leave_requests') ? (bool)$request->block_leave_requests : false,
            'allow_attendance_exception' => $request->has('allow_attendance_exception') ? (bool)$request->allow_attendance_exception : false,
        ]);
        
        $notification = notify(__("Holiday has been updated"));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $public_holiday)
    {
        $holiday = $public_holiday;
        $holiday->delete();
        $notification = notify(__("Holiday has been deleted"));
        return back()->with($notification);
    }
}

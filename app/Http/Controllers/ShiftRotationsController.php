<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftRotation;
use App\Models\ShiftRotationStep;
use App\Models\UserShiftRotation;
use App\Models\User;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftRotationsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!AttendanceSetting::get('shifts_enabled', true)) {
                return redirect()->route('dashboard')->with('error', __('Shifts module is currently disabled.'));
            }
            if (!AttendanceSetting::get('rotational_shift_enabled', false)) {
                return redirect()->route('shifts.index')->with('error', __('Rotational shifts are currently disabled in Attendance Settings.'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of shift rotations.
     */
    public function index()
    {
        $pageTitle = __('Shift Rotations');
        $rotations = ShiftRotation::withCount('steps')
            ->orderBy('name')
            ->get();

        return view('pages.shifts.rotation.index', compact('pageTitle', 'rotations'));
    }

    /**
     * Show the form for creating a new rotation.
     */
    public function create()
    {
        $pageTitle = __('Create Shift Rotation Plan');
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();

        return view('pages.shifts.rotation.create', compact('pageTitle', 'shifts'));
    }

    /**
     * Store a newly created rotation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'shifts' => 'required|array|min:1',
            'shifts.*' => 'exists:shifts,id',
        ]);

        try {
            DB::beginTransaction();

            $companyId = auth()->user()->company_id ?? auth()->user()->business_id;

            if (!$companyId) {
                throw new \Exception(__('No valid company or business associated with your account.'));
            }

            $rotation = ShiftRotation::create([
                'company_id' => $companyId,
                'name' => $request->name,
                'frequency_type' => $request->frequency_type,
                'frequency_interval' => $request->frequency_interval,
                'start_date' => $request->start_date,
                'description' => $request->description,
                'is_active' => true,
            ]);

            foreach ($request->shifts as $index => $shiftId) {
                ShiftRotationStep::create([
                    'shift_rotation_id' => $rotation->id,
                    'shift_id' => $shiftId ?: null,
                    'step_order' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()->route('shifts.rotation.index')->with('success', __('Rotation plan created successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Rotation Plan Creation Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('Error creating rotation plan: ') . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the rotation.
     */
    public function edit(ShiftRotation $rotation)
    {
        $pageTitle = __('Edit Rotation Plan');
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();
        $rotation->load('steps.shift');

        return view('pages.shifts.rotation.edit', compact('pageTitle', 'rotation', 'shifts'));
    }

    /**
     * Update the rotation.
     */
    public function update(Request $request, ShiftRotation $rotation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'frequency_type' => 'required|in:daily,weekly,monthly',
            'frequency_interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'shifts' => 'required|array|min:1',
            'shifts.*' => 'exists:shifts,id',
        ]);

        try {
            DB::beginTransaction();

            $rotation->update([
                'name' => $request->name,
                'frequency_type' => $request->frequency_type,
                'frequency_interval' => $request->frequency_interval,
                'start_date' => $request->start_date,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Rebuild steps
            $rotation->steps()->delete();
            foreach ($request->shifts as $index => $shiftId) {
                ShiftRotationStep::create([
                    'shift_rotation_id' => $rotation->id,
                    'shift_id' => $shiftId ?: null,
                    'step_order' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()->route('shifts.rotation.index')->with('success', __('Rotation plan updated successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Rotation Plan Update Error: ' . $e->getMessage(), [
                'rotation_id' => $rotation->id,
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('Error updating rotation plan: ') . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the rotation.
     */
    public function destroy(ShiftRotation $rotation)
    {
        $rotation->delete();
        return redirect()->route('shifts.rotation.index')->with('success', __('Rotation plan deleted successfully.'));
    }

    /**
     * Show the forecasting page.
     */
    public function forecast()
    {
        $pageTitle = __('Shift Forecasting');
        $employees = User::where('type', 'Employee')
            ->where('is_active', 1)
            ->with(['employeeDetail.department'])
            ->orderBy('firstname')
            ->get();
            
        $departments = \App\Models\Department::orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->orderBy('name')->get();

        return view('pages.shifts.rotation.forecast', compact('pageTitle', 'employees', 'departments', 'shifts'));
    }

    /**
     * Get forecast data via AJAX.
     */
    public function getForecastData(Request $request)
    {
        try {
            $userId = $request->user_id;
            $shiftId = $request->shift_id;
            $startDate = \Illuminate\Support\Carbon::parse($request->start_date ?? now());
            $endDate = \Illuminate\Support\Carbon::parse($request->end_date ?? now()->addDays(30));
            
            $forecast = [];
            $currentDate = $startDate->copy();

            // Load rotation once if we are in shift-forecast mode
            $rotation = null;
            $baseShiftId = null;
            $fixedShift = null;
            
            if ($shiftId && !$userId) {
                $baseShiftId = $shiftId;
                
                // Try to find a rotation plan that includes this shift
                $rotation = ShiftRotation::where('is_active', true)
                    ->whereHas('steps', function ($query) use ($baseShiftId) {
                        $query->where('shift_id', $baseShiftId);
                    })
                    ->with(['steps.shift'])
                    ->first();
                
                // If no rotation found, load the shift as a fixed schedule
                if (!$rotation) {
                    $fixedShift = Shift::where('id', $baseShiftId)
                        ->where('is_active', true)
                        ->first();
                }
            }
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $shift = null;

                if ($userId) {
                    // Employee-based forecast
                    $shift = \App\Models\UserShift::getShiftForUser($userId, $dateStr);
                } elseif ($rotation) {
                    // Rotation-based forecast
                    $steps = $rotation->steps;
                    $rotationStartDate = $rotation->start_date;
                    $interval = $rotation->frequency_interval > 0 ? $rotation->frequency_interval : 1;
                    
                    // Calculate the number of complete intervals that have passed
                    $diff = 0;
                    switch ($rotation->frequency_type) {
                        case 'daily':
                            $diff = $rotationStartDate->diffInDays($currentDate, false);
                            break;
                        case 'weekly':
                            // For weekly rotations, calculate based on week numbers
                            // This ensures the shift stays the same for the entire week
                            $startWeek = $rotationStartDate->copy()->startOfWeek();
                            $currentWeek = $currentDate->copy()->startOfWeek();
                            $diff = $startWeek->diffInWeeks($currentWeek, false);
                            break;
                        case 'monthly':
                            $diff = (($currentDate->year - $rotationStartDate->year) * 12) + ($currentDate->month - $rotationStartDate->month);
                            break;
                    }

                    // Calculate which step in the rotation we're at
                    $cycleNumber = floor($diff / $interval);
                    $stepIndex = $steps->search(fn($s) => $s->shift_id == $baseShiftId);
                    $offset = ($stepIndex !== false) ? $stepIndex : 0;
                    $stepCount = $steps->count();
                    $currentIndex = ($cycleNumber + $offset) % $stepCount;
                    if ($currentIndex < 0) $currentIndex = ($stepCount + $currentIndex) % $stepCount;
                    
                    $resolvedShift = $steps[$currentIndex]->shift;
                    if ($resolvedShift && $resolvedShift->is_active && $resolvedShift->isWorkDay($currentDate->dayOfWeek)) {
                        $shift = $resolvedShift;
                    }
                } elseif ($fixedShift) {
                    // Fixed shift forecast (not part of rotation)
                    if ($fixedShift->isWorkDay($currentDate->dayOfWeek)) {
                        $shift = $fixedShift;
                    }
                }
                
                $forecast[] = [
                    'date' => $dateStr,
                    'day' => $currentDate->format('l'),
                    'shift_name' => $shift ? $shift->name : __('Off Day'),
                    'start_time' => $shift ? date('h:i A', strtotime($shift->start_time)) : '-',
                    'end_time' => $shift ? date('h:i A', strtotime($shift->end_time)) : '-',
                    'is_off' => $shift ? false : true,
                ];
                
                $currentDate->addDay();
            }
            
            return response()->json([
                'success' => true,
                'data' => $forecast
            ]);
        } catch (\Exception $e) {
            \Log::error('Forecast generation error: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'shift_id' => $request->shift_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => __('Error generating forecast: ') . $e->getMessage()
            ], 500);
        }
    }
}

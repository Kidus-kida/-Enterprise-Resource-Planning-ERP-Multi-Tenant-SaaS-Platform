<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\UserShift;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!attendance_setting('shifts_enabled', true)) {
                return redirect()->route('dashboard')->with('error', __('Shifts module is currently disabled in Attendance Settings.'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of shifts
     */
    public function index()
    {
        $pageTitle = __('Shifts Management');
        
        $shifts = Shift::withCount('userShifts')
            ->orderBy('name')
            ->get();

        $shiftMode = AttendanceSetting::get('shift_mode', 'mandatory');
        $shiftsEnabled = AttendanceSetting::get('shifts_enabled', true);
        
        return view('pages.shifts.index', compact('pageTitle', 'shifts', 'shiftMode', 'shiftsEnabled'));
    }

    /**
     * Show the form for creating a new shift
     */
    public function create()
    {
        $pageTitle = __('Create Shift');
        $defaultGracePeriod = AttendanceSetting::get('grace_in_minutes', 15);
        $defaultGraceOut = AttendanceSetting::get('grace_out_minutes', 10);
        
        // Fetch global working days to use as defaults
        $globalWorkingDays = AttendanceSetting::get('working_days', []); // ['monday', 'tuesday', ...]
        $dayMap = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 
            'friday' => 5, 'saturday' => 6, 'sunday' => 7
        ];
        
        $defaultWorkDays = [];
        foreach ($globalWorkingDays as $dayAbbr) {
            if (isset($dayMap[$dayAbbr])) {
                $defaultWorkDays[] = $dayMap[$dayAbbr];
            }
        }
        
        // Fallback if none defined
        if (empty($defaultWorkDays)) {
            $defaultWorkDays = [1, 2, 3, 4, 5];
        }

        return view('pages.shifts.create', compact('pageTitle', 'defaultGracePeriod', 'defaultGraceOut', 'defaultWorkDays'));
    }

    /**
     * Store a newly created shift
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shifts,code',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'grace_out_minutes' => 'nullable|integer|min:0|max:60',
            'work_days' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Night Shift Restriction Check
        if (!$this->validateNightShiftRestriction($request->start_time, $request->end_time, $errorMessage)) {
            return back()->withErrors(['start_time' => $errorMessage])->withInput();
        }

        $validated['work_days'] = json_encode($request->work_days ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        Shift::create($validated);

        $notification = notify(__('Shift created successfully'));
        return redirect()->route('shifts.index')->with($notification);
    }

    /**
     * Show the form for editing a shift
     */
    public function edit(Shift $shift)
    {
        $pageTitle = __('Edit Shift');
        
        return view('pages.shifts.edit', compact('pageTitle', 'shift'));
    }

    /**
     * Update the specified shift
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shifts,code,' . $shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'grace_out_minutes' => 'nullable|integer|min:0|max:60',
            'work_days' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Night Shift Restriction Check
        if (!$this->validateNightShiftRestriction($request->start_time, $request->end_time, $errorMessage)) {
            return back()->withErrors(['start_time' => $errorMessage])->withInput();
        }

        $validated['work_days'] = json_encode($request->work_days ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        $shift->update($validated);

        $notification = notify(__('Shift updated successfully'));
        return redirect()->route('shifts.index')->with($notification);
    }

    /**
     * Validate if a shift timing is allowed based on night shift settings
     */
    private function validateNightShiftRestriction($startTime, $endTime, &$errorMessage): bool
    {
        $nightShiftEnabled = AttendanceSetting::get('night_shift_enabled', true);
        if ($nightShiftEnabled) {
            return true;
        }

        // Check if it's a night shift (crosses midnight)
        if ($endTime < $startTime) {
            $errorMessage = __('Night shifts (crossing midnight) are currently disabled in Attendance Settings.');
            return false;
        }

        // Check for overlap with restricted night time range
        $nightStart = AttendanceSetting::get('night_time_start', '22:00');
        $nightEnd = AttendanceSetting::get('night_time_end', '06:00');

        // Simple overlap check (assuming both ranges are within same day for simplicity of comparison)
        // A shift overlaps with night time if:
        // shift_start < night_end AND shift_end > night_start
        
        // Handling midnight crossing in restricted range (e.g. 22:00 - 06:00)
        if ($nightEnd < $nightStart) {
            // Restricted range crosses midnight. Check two segments: [nightStart, 23:59] and [00:00, nightEnd]
            $overlapsSegment1 = ($startTime < '23:59' && $endTime > $nightStart);
            $overlapsSegment2 = ($startTime < $nightEnd && $endTime > '00:00');
            
            if ($overlapsSegment1 || $overlapsSegment2) {
                $errorMessage = __('This shift overlaps with the restricted night time range (:start - :end).', 
                    ['start' => $nightStart, 'end' => $nightEnd]);
                return false;
            }
        } else {
            // Restricted range is within same day
            if ($startTime < $nightEnd && $endTime > $nightStart) {
                $errorMessage = __('This shift overlaps with the restricted night time range (:start - :end).', 
                    ['start' => $nightStart, 'end' => $nightEnd]);
                return false;
            }
        }

        return true;
    }

    /**
     * Remove the specified shift
     */
    public function destroy(Shift $shift)
    {
        DB::beginTransaction();
        try {
            // Deactivate all assignments associated with this shift
            $shift->userShifts()->update(['is_active' => 0]);

            // Delete the shift
            $shift->delete();

            DB::commit();

            $notification = notify(__('Shift and associated assignments removed successfully'));
            return redirect()->route('shifts.index')->with($notification);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Shift deletion error: ' . $e->getMessage());
            
            $notification = notify(__('Failed to delete shift'), 'error');
            return redirect()->back()->with($notification);
        }
    }

    /**
     * Show the shift assignment page
     */
    public function assign()
    {
        $pageTitle = __('Assign Employees to Shifts');
        
        $shifts = Shift::orderBy('name')->get();
        $employees = User::where('type', 'Employee')
            ->where('is_active', 1)
            ->with(['employeeDetail.department', 'employeeDetail.designation'])
            ->orderBy('firstname')
            ->get();

        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        // Get current assignments
        $assignments = UserShift::with(['user', 'shift'])
            ->where('is_active', 1)
            ->get()
            ->groupBy('shift_id');

        return view('pages.shifts.assign', compact('pageTitle', 'shifts', 'employees', 'assignments', 'departments', 'designations'));
    }

    /**
     * Store shift assignment
     */
    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['user_ids'] as $userId) {
                // Deactivate old assignments for this user
                UserShift::where('user_id', $userId)
                    ->where('is_active', 1)
                    ->update(['is_active' => 0]);

                // Create new assignment
                UserShift::create([
                    'user_id' => $userId,
                    'shift_id' => $validated['shift_id'],
                    'effective_from' => $validated['effective_from'],
                    'effective_until' => $validated['effective_until'] ?? null,
                    'is_active' => 1,
                ]);
            }

            DB::commit();

            $notification = notify(__('Employees assigned to shift successfully'));
            return redirect()->route('shifts.assign')->with($notification);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Shift assignment error: ' . $e->getMessage());
            
            $notification = notify(__('Failed to assign employees'), 'error');
            return redirect()->back()->with($notification);
        }
    }

    /**
     * Remove employee from shift
     */
    public function removeAssignment(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:user_shifts,id',
        ]);

        UserShift::where('id', $validated['assignment_id'])
            ->update(['is_active' => 0]);

        $notification = notify(__('Employee removed from shift'));
        return redirect()->back()->with($notification);
    }
}

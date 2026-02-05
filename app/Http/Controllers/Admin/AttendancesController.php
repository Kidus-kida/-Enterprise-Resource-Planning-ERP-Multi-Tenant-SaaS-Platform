<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserType;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
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

    public function edit(User $user, $date)
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('startDate', $date)
            ->first();

        // If attendance doesn't exist, we'll create a dummy one for the view
        // so the user can "add" a missing day through the same interface
        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => $user->id,
                'startDate' => $date,
                'endDate' => $date
            ]);
        }

        $timestamps = $attendance->exists ? $attendance->timestamps : collect();
        
        return view('pages.attendances.edit', compact('user', 'date', 'attendance', 'timestamps'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'reason' => \App\Models\AttendanceSetting::get('correction_require_reason', true) ? 'required|min:5' : 'nullable',
        ]);

        try {
            \DB::beginTransaction();

            $attendance = Attendance::firstOrCreate(
                ['user_id' => $request->user_id, 'startDate' => $request->date],
                ['endDate' => $request->date]
            );

            // Update or Create the timestamp
            $timestamp = $attendance->timestamps()->first();
            
            $oldData = $timestamp ? $timestamp->toArray() : null;

            if (!$timestamp) {
                $timestamp = new \App\Models\AttendanceTimestamp([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                ]);
            }

            if ($request->start_time) {
                $timestamp->startTime = Carbon::parse($request->date . ' ' . $request->start_time);
            }
            if ($request->end_time) {
                $timestamp->endTime = Carbon::parse($request->date . ' ' . $request->end_time);
            }
            
            $timestamp->save();

            // Audit Trail Logic
            if (\App\Models\AttendanceSetting::get('correction_audit_trail_enabled', true)) {
                \Log::info('Attendance Correction:', [
                    'admin_id' => \Auth::id(),
                    'user_id' => $request->user_id,
                    'date' => $request->date,
                    'old_data' => $oldData,
                    'new_data' => $timestamp->toArray(),
                    'reason' => $request->reason
                ]);
            }

            \DB::commit();
            return response()->json(['success' => true, 'message' => __('Attendance corrected successfully.')]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Attendance Correction Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Failed to correct attendance.')], 500);
        }
    }
}

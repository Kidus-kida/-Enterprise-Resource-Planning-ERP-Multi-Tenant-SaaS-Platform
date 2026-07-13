<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use Livewire\Attributes\Js;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;
use App\Models\AttendanceTimestamp;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;


class EmployeeAttendance extends Component
{
    public $forProject,$project, $clockedIn=false, $timeStarted;
    public $totalHours = 0;
    public $timeId = null;
    public $attendances, $todayActivity;
    
    public $totalHoursToday;
    public $totalHoursThisMonth;
    public $totalHoursThisWeek;

    public $latitude;
    public $longitude;
    protected $listeners = ['setLocationCoords'];

    public function setLocationCoords($coords)
    {
        $this->latitude = $coords['lat'];
        $this->longitude = $coords['lng'];
    }

    public function mount()
    {
        $this->getClockInData(); 
    }



    public function getLocationNameFromCoords($lat, $lng)
    {
        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'ERP') . '/1.0'
        ])->get("https://nominatim.openstreetmap.org/reverse", [
            'format' => 'json',
            'lat' => $lat,
            'lon' => $lng,
            'zoom' => 18,
            'addressdetails' => 1,
        ]);

        // dd($response->json()['display_name']);

        if ($response->successful()) {
            return $response->json()['display_name'] ?? null;
        }

        return null;
    }


    public function clockin()
    {
        try{
            $locationName = null;
            if ($this->latitude && $this->longitude) {
                $locationName = $this->getLocationNameFromCoords($this->latitude, $this->longitude);
            }
            // dd($locationName);
            $user = auth()->user();
            
            // --- Shift Mode Integration ---
            $now = now();
            $resolvedShift = \App\Models\UserShift::getShiftForTime($user->id, $now);
            $shiftMode = \App\Models\AttendanceSetting::get('shift_mode', 'optional');
            $shiftsEnabled = \App\Models\AttendanceSetting::get('shifts_enabled', false);

            if ($shiftsEnabled && $shiftMode === 'mandatory' && !$resolvedShift) {
                $this->dispatch('Notification', __('You do not have an assigned shift for this time. Clock-in denied.'));
                return;
            }
            // ------------------------------

            // check if user is clocked in
            $existingActiveTimestamp = AttendanceTimestamp::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->whereNull('endTime')
            ->first();

            if ($existingActiveTimestamp) {
                $this->dispatch('Notification', __('You are already clocked in.'));
                return;
            }


            if($this->forProject){
                $this->validate([
                    'project' => 'required',
                ]);
            }
            $todayAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('created_at', Carbon::today())->first();
            if(!empty($todayAttendance)){
                $attendance = $todayAttendance;
            }else{
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'startDate' => now(),
                    'endDate' => null,
                ]);
            }
            // dd($locationName);
            AttendanceTimestamp::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'project_id' => $this->project,
                'shift_id' => $resolvedShift ? $resolvedShift->id : null, // NEW: Link to shift
                'startTime' => now(),
                'endTime' => null,
                // 'location' => $user->employeeDetail->department->location ?? null,
                'location' => $locationName ?? ($user->employeeDetail->department->location ?? null),
                'billable' => false,
                'ip' => request()->ip() ?? null,
            ]);
            // $this->clockedIn = true;
            $this->dispatch('IsClockedIn');
            $this->dispatch('refreshAttendance');
            $this->dispatch('Notification',__('You have clockin successfully'));
            // $this->js("bootstrap.Modal.getInstance(document.getElementById('clockin_modal')).hide()");
            $this->latitude = null;
            $this->longitude = null;
        }catch(\Exception $e){
            $this->dispatch('Notification',__('Something went wrong'));
        }
    }

    public function clockout($timestampId)
    {
        try{
            $locationName = null;
            if ($this->latitude && $this->longitude) {
                $locationName = $this->getLocationNameFromCoords($this->latitude, $this->longitude);
            }
            $timestamp = AttendanceTimestamp::find(Crypt::decrypt($timestampId));
            $timestamp->attendance->update([
                'endDate' => now(),
            ]);
            $timestamp->update([
                'endTime' => now(),
                'co_location' => $locationName ?? null,
            ]);
            // $this->clockedIn = false;
            $this->dispatch('IsClockedIn');
            $this->dispatch('refreshAttendance');
            $this->dispatch('Notification',__('You have clockout successfully'));
            $this->latitude = null;
            $this->longitude = null;
        }catch(\Exception $e){
            $this->dispatch('Notification',__('Something went wrong'));
        }
    }

   
    #[On('refreshAttendance')]
    public function getAttendance()
    {
        $userId = auth()->user()->id;
        $attendances = AttendanceTimestamp::where('user_id', $userId)
                    ->whereNotNull('attendance_id');
        $this->attendances = $attendances->get();
        $this->todayActivity = $attendances->whereDate('created_at', Carbon::today())->get();
    }

    #[On('fetchStatistics')]
    public function statistics()
    {
        $userId = auth()->user()->id;
        $userAttendances = AttendanceTimestamp::where('user_id', $userId)
                        ->whereNotNull('attendance_id');
        $this->totalHoursToday = $userAttendances->whereDate('created_at', Carbon::today())
                        ->get()
                        ->sum('totalHours');
        $this->totalHoursThisMonth = $userAttendances->whereMonth('created_at', Carbon::now())
                        ->get()
                        ->sum('totalHours');
        $this->totalHoursThisWeek = $userAttendances
                        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                        ->get()
                        ->sum('totalHours');
    }

    #[On('IsClockedIn')]
    public function getClockInData()
    {
        $todayClockin = Attendance::where('user_id', auth()->user()->id)
                    ->whereDate('created_at', Carbon::today())
                    ->first();
    
        if(!empty($todayClockin)){
            $latestClockin = $todayClockin->timestamps()->latest()->whereNull('endTime')->first() ?? null;
            if(!empty($latestClockin)){
                $this->clockedIn = true; // ✅ This was missing
                $this->timeId = Crypt::encrypt($latestClockin->id);
                $this->timeStarted = $latestClockin->startTime;
                $this->totalHours = Carbon::now()->diff($latestClockin->startTime)->h;
            } else {
                $this->clockedIn = false; // ✅ Also set explicitly
            }
        } else {
            $this->clockedIn = false;
        }
    }

   
    public function render()
    {
        return view('livewire.employee-attendance');
    }
    
}

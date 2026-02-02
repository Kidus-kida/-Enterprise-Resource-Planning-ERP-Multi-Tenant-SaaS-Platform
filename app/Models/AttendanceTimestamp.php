<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceTimestamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','attendance_id','project_id','shift_id','startTime','endTime','location','co_location',
        'billable','ip','note','latitude','longitude','co_latitude','co_longitude'
    ];

    protected $casts = [
        'startTime' => 'datetime:H:i:s',
        'endTime' => 'datetime:H:i:s',
    ];

    public function getTotalHoursAttribute()
    {
        return !empty($this->endTime) ? $this->endTime->diff($this->startTime)->hour: now()->diff($this->startTime)->hour;
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function isLate(): bool
    {
        if (!$this->shift || !$this->startTime) return false;
        
        $shiftStart = Carbon::parse($this->startTime->format('Y-m-d') . ' ' . $this->shift->start_time);
        $graceMinutes = $this->shift->grace_in_minutes ?? \App\Models\AttendanceSetting::get('grace_in_minutes', 0);
        
        return $this->startTime->gt($shiftStart->addMinutes($graceMinutes));
    }

    public function isEarlyLeave(): bool
    {
        if (!$this->shift || !$this->endTime) return false;
        
        $shiftEnd = Carbon::parse($this->endTime->format('Y-m-d') . ' ' . $this->shift->end_time);
        if ($this->shift->isNightShift() && $this->endTime->hour < 20) {
            // End time is likely the next day
        }

        $graceMinutes = $this->shift->grace_out_minutes ?? \App\Models\AttendanceSetting::get('grace_out_minutes', 0);
        
        return $this->endTime->lt($shiftEnd->subMinutes($graceMinutes));
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function project(){
        return $this->belongsTo(\Modules\Project\Models\Project::class, 'project_id');
    }
}

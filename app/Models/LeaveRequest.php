<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'leave_start_date',
        'leave_end_date',
        'request_reason',
        'attachements',
        'half_day',
        'multiple_day',
        'reject_reason',
        'attended_by',
        'status'

    ];
    protected $casts = [
        'attachements' => 'array',
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
     public function admin()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
    

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends TenantModel
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
        'status',
        'current_approval_level',
        'required_approval_levels',
        'total_days',
        'request_type',
        'total_hours',
        'approval_chain',
        'is_cancelled',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'is_emergency',
        'admin_notes'

    ];
    protected $casts = [
        'attachements' => 'array',
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // Alias for employee relationship (for consistency with other code)
    public function user()
    {
        return $this->employee();
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


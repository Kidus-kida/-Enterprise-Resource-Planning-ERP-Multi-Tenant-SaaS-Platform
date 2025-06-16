<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnunalLeave extends Model
{
    /* -----------------------------------------------------------------
    |  Database
    |------------------------------------------------------------------*/
    protected $table = 'anunal_leaves';       // adjust if your table name differs

    /** Allow everything or explicitly whitelist; choose ONE style */
    // protected $guarded = [];               // ← simplest (unguarded)
    protected $fillable = [
        'employee_id',
        'current_year',
        'previous_year',
        'year_bpy',
        'per_month',
        'per_year',
        'total_anunal_leave',
    ];

    /** Keep 2‑decimal precision when attributes are cast to/from PHP */
    protected $casts = [
        'current_year' => 'decimal:2',
        'previous_year' => 'decimal:2',
        'per_month' => 'decimal:2',
        'per_year' => 'decimal:2',
        'total_anunal_leave' => 'decimal:2',
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}

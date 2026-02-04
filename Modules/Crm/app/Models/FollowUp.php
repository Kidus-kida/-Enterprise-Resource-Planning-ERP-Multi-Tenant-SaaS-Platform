<?php

namespace Modules\Crm\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Crm\Models\Lead;

class FollowUp extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'title',
        'description',
        'follow_up_date',
        'status',
        'assigned_to',
        'created_by'
    ];

    protected $casts = [
        'follow_up_date' => 'datetime'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
    
    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }
}

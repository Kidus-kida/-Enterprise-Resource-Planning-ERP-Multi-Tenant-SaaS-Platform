<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Performance extends TenantModel
{
    protected $casts = [
        'employment_date' => 'date',
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'employee_signed_at' => 'datetime',
        'supervisor_signed_at' => 'datetime',
        'supervisors_supervisor_signed_at' => 'datetime',
        'evaluator_signed_at' => 'datetime',
        'employee_signed_date' => 'datetime',
        'department_manager_signed_at' => 'datetime',
        'hr_received_date' => 'datetime',
    ];

    protected $fillable = [
        'staff_name',
        'department',
        'employment_date',
        'job_title',
        'promotion',
        'transfer',
        'evaluation_period_start',
        'evaluation_period_end',
        'knowledge_of_job',
        'quality_of_work',
        'quantity_of_work',
        'emotional_intelligence',
        'time_management',
        'initiative_and_creativity',
        'team_work',
        'accountablity',
        'attendance_and_punctuality',
        'company_resource_usage_and_protection',
        'communication_skills',
        'average_score',
        'performance_result',
        'rating_remark',
        'employee_signed_at',
        'supervisor_signed_at',
        'supervisor_name',
        'supervisor_signed_at',
        'evaluator_name',
        'evaluator_signed_at',
        'employee_name_signed',
        'employee_signed_date',
        'department_manager',
        'department_manager_signed_at',
        'hr_received_date',
        'needs_improvement',
        'improvement_action',
        'unmet_expectations',
        'faced_problems',
        'problem_solution',
        'other_comment',
        'created_at',
        'updated_at',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}


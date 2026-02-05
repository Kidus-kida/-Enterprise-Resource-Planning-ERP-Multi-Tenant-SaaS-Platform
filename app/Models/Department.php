<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends TenantModel
{
    use HasFactory;
    use \App\Traits\HasCompany;

    protected $fillable = [
        'name', 'location','description', 'manager_id', 'color', 'company_name', 'company_id', 'parent_id', 'is_active'
    ];

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class);
    }
    
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }


}

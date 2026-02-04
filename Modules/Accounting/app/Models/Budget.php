<?php

namespace Modules\Accounting\Models;

use App\Models\TenantModel;

use Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCompany;
use Modules\Accounting\Database\Factories\BudgetFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Budget extends TenantModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title','type','startDate','endDate','total_revenue','total_expense',
        'profit','budget_category_id','project_id','taxes','amount','note',
        'company_id'
    ];

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function expenses()
    {
        return $this->hasMany(ExpenseBudget::class);
    }

    public function revenue(){
        return $this->hasMany(RevenueBudget::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    protected static function newFactory(): BudgetFactory
    {
        return BudgetFactory::new();
    }
}


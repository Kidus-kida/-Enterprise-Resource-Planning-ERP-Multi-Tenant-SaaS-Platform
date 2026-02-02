<?php

namespace Modules\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCompany;

class Expense extends Model
{
    use HasFactory, HasCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'item_name','purchased_from','purchase_date','amount','status','paid_by','created_by',
        'company_id'
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}

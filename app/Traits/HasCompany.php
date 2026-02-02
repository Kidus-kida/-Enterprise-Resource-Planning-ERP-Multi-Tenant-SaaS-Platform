<?php

namespace App\Traits;

use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Session;

trait HasCompany
{
    /**
     * Boot the HasCompany trait for a model.
     *
     * @return void
     */
    public static function bootHasCompany()
    {
        // Only apply the scope if the column exists
        try {
            $model = new static;
            $tableName = $model->getTable();
            if (\Illuminate\Support\Facades\Schema::hasColumn($tableName, 'company_id')) {
                static::addGlobalScope(new CompanyScope);
                
                // Auto-assign company_id when creating
                static::creating(function ($model) {
                    if (!array_key_exists('company_id', $model->getAttributes())) {
                        // Only auto-assign if we have a session (not during seeding/console)
                        if (request()->hasSession()) {
                            $company_id = request()->session()->get('user.company_id');
                            if (!empty($company_id)) {
                                $model->company_id = $company_id;
                            }
                        }
                    }
                });
            }
        } catch (\Exception $e) {
            // Silently fail if schema check fails (e.g., during migrations)
        }
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(\App\Company::class, 'company_id');
    }

    /**
     * Scope a query to only include specific company.
     */
    public function scopeForCompany($query, $company_id)
    {
        return $query->where('company_id', $company_id);
    }
    
    /**
     * Scope a query to only include shared resources.
     */
    public function scopeShared($query)
    {
        return $query->whereNull('company_id');
    }
}

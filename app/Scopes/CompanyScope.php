<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // SAFETY BYPASS: Never apply this scope on the emergency fix route
        if (request()->is('emergency-db-fix')) {
            return;
        }

        // Skip scope if there's no session (e.g., during seeding, console commands)
        if (!request()->hasSession()) {
            return;
        }

        // Prevent infinite recursion: Check session directly
        if (request()->session()->has('user.company_id') || request()->session()->has('user.active_company_ids')) {
            
            $active_ids = request()->session()->get('user.active_company_ids', []);
            $company_id = request()->session()->get('user.company_id');

            // Use qualified column name
            $column = $model->qualifyColumn('company_id');

            // If we have multiple active companies selected
            if (!empty($active_ids)) {
                $builder->where(function ($query) use ($active_ids, $column) {
                    $query->whereIn($column, $active_ids)
                          ->orWhereNull($column);
                });
            }
            // Fallback to single company ID if set
            elseif (!empty($company_id)) {
                $builder->where(function ($query) use ($company_id, $column) {
                    $query->where($column, $company_id)
                          ->orWhereNull($column);
                });
            }
        }
    }
}

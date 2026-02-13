<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccrualPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveAccrualPlan::query();

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Filters
        if ($request->has('filter')) {
            if ($request->filter == 'worked_time') {
                $query->where('is_based_on_worked_time', true);
            } elseif ($request->filter == 'active') {
                $query->where('is_active', true);
            }
        }

        $accrualPlans = $query->with('levels')->get();
        $pageTitle = __('Accrual Plans');

        $searchConfig = [
            'action' => route('leave.config.accrual-plans.index'),
            'fields' => [
                ['key' => 'search', 'label' => 'Name or Description'],
            ],
            'filters' => [
                ['label' => 'Active Only', 'value' => 'active'],
                ['label' => 'Based on Worked Time', 'value' => 'worked_time'],
            ],
        ];

        return view('leave.configuration.accrual-plans.index', compact('accrualPlans', 'pageTitle', 'searchConfig'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = __('Create Accrual Plan');
        return view('leave.configuration.accrual-plans.create', compact('pageTitle'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accrualPlan = LeaveAccrualPlan::with('levels')->findOrFail($id);
        $pageTitle = __('Accrual Plan Details');
        return view('leave.configuration.accrual-plans.show', compact('accrualPlan', 'pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'accrued_gain_time' => 'required|in:start,end',
            'carry_over_time' => 'required|in:year_start,allocation,other',
            'carry_over_day' => 'nullable|integer|min:1|max:31',
            'carry_over_month' => 'nullable|integer|min:1|max:12',
            'is_based_on_worked_time' => 'boolean',
            'transition_mode' => 'required|in:immediately,after_accrual',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'odoo_id' => 'nullable|integer', // Odoo field
            'odoo_name' => 'nullable|string|max:255', // Odoo field

            // Levels Validation
            'levels' => 'required|array|min:1',
            'levels.*.sequence' => 'required|integer',
            'levels.*.start_count' => 'required|integer|min:0',
            'levels.*.start_type' => 'nullable|in:days,months,years',
            'levels.*.accrual_amount' => 'required|numeric|min:0',
            'levels.*.accrual_unit' => 'required|in:days,hours',
            'levels.*.accrual_frequency' => 'required|in:hourly,daily,weekly,biweekly,monthly,biyearly,yearly',
            'levels.*.yearly_cap' => 'nullable|numeric|min:0',
            'levels.*.yearly_cap_unit' => 'nullable|in:days,hours',
            'levels.*.cap_accrued_time' => 'nullable|numeric|min:0',
            'levels.*.balance_cap_unit' => 'nullable|in:days,hours',
            'levels.*.action_with_unused_accruals' => 'required|in:lost,all,maximum',
            'levels.*.max_carryover' => 'nullable|numeric|min:0',
            'levels.*.max_carryover_unit' => 'nullable|in:days,hours',
            'levels.*.carryover_validity_period' => 'nullable|integer|min:1',
            'levels.*.odoo_id' => 'nullable|integer',
        ]);

        \DB::transaction(function () use ($validated) {
            $plan = LeaveAccrualPlan::create(\Arr::except($validated, ['levels']));

            foreach ($validated['levels'] as $levelData) {
                $plan->levels()->create($levelData);
            }
        });

        return redirect()->route('leave.config.accrual-plans.index')
            ->with('success', __('Accrual Plan created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $accrualPlan = LeaveAccrualPlan::with('levels')->findOrFail($id);
        $pageTitle = __('Edit Accrual Plan');
        return view('leave.configuration.accrual-plans.edit', compact('accrualPlan', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accrualPlan = LeaveAccrualPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'accrued_gain_time' => 'required|in:start,end',
            'carry_over_time' => 'required|in:year_start,allocation,other',
            'carry_over_day' => 'nullable|integer|min:1|max:31',
            'carry_over_month' => 'nullable|integer|min:1|max:12',
            'is_based_on_worked_time' => 'boolean',
            'transition_mode' => 'required|in:immediately,after_accrual',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'odoo_id' => 'nullable|integer',
            'odoo_name' => 'nullable|string|max:255',

            // Levels Validation
            'levels' => 'required|array|min:1',
            'levels.*.id' => [
                'nullable',
                Rule::exists(\App\Models\LeaveAccrualLevel::class, 'id')->where('leave_accrual_plan_id', $id)
            ],
            'levels.*.sequence' => 'required|integer',
            'levels.*.start_count' => 'required|integer|min:0',
            'levels.*.start_type' => 'nullable|in:days,months,years',
            'levels.*.accrual_amount' => 'required|numeric|min:0',
            'levels.*.accrual_unit' => 'required|in:days,hours',
            'levels.*.accrual_frequency' => 'required|in:hourly,daily,weekly,biweekly,monthly,biyearly,yearly',
            'levels.*.yearly_cap' => 'nullable|numeric|min:0',
            'levels.*.yearly_cap_unit' => 'nullable|in:days,hours',
            'levels.*.cap_accrued_time' => 'nullable|numeric|min:0',
            'levels.*.balance_cap_unit' => 'nullable|in:days,hours',
            'levels.*.action_with_unused_accruals' => 'required|in:lost,all,maximum',
            'levels.*.max_carryover' => 'nullable|numeric|min:0',
            'levels.*.max_carryover_unit' => 'nullable|in:days,hours',
            'levels.*.carryover_validity_period' => 'nullable|integer|min:1',
        ]);

        \DB::transaction(function () use ($accrualPlan, $validated) {
            $accrualPlan->update(\Arr::except($validated, ['levels']));

            // Get IDs provided in the request
            $providedIds = collect($validated['levels'])
                ->pluck('id')
                ->filter()
                ->toArray();
            
            // Delete levels that are not in the request
            $accrualPlan->levels()->whereNotIn('id', $providedIds)->delete();

            foreach ($validated['levels'] as $levelData) {
                if (!empty($levelData['id'])) {
                    // Update existing level
                    $accrualPlan->levels()->where('id', $levelData['id'])->first()?->update($levelData);
                } else {
                    // Create new level
                    $accrualPlan->levels()->create($levelData);
                }
            }
        });

        return redirect()->route('leave.config.accrual-plans.index')
            ->with('success', __('Accrual Plan updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accrualPlan = LeaveAccrualPlan::findOrFail($id);
        $accrualPlan->delete();

        return redirect()->route('leave.config.accrual-plans.index')
            ->with('success', __('Accrual Plan deleted successfully.'));
    }
}


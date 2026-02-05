<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class AccrualPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveAccrualPlan::with('leaveType');

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }
        
        // Filters
        if ($request->has('filter')) {
            if ($request->filter == 'monthly') {
                $query->where('accrual_frequency', 'monthly');
            } elseif ($request->filter == 'yearly') {
                $query->where('accrual_frequency', 'yearly');
            } elseif ($request->filter == 'active') {
                $query->where('is_active', true);
            }
        }

        $accrualPlans = $query->get();
        $pageTitle = __('Accrual Plans');
        
        $searchConfig = [
            'action' => route('leave.config.accrual-plans.index'),
            'fields' => [
                ['key' => 'search', 'label' => 'Name or Description'],
            ],
            'filters' => [
                ['label' => 'Active Only', 'value' => 'active'],
                ['label' => 'Monthly Plans', 'value' => 'monthly'],
                ['label' => 'Yearly Plans', 'value' => 'yearly'],
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
        $leaveTypes = LeaveType::all(); // Assuming all leave types can have accruals for now
        return view('leave.configuration.accrual-plans.create', compact('pageTitle', 'leaveTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'leave_type_id' => 'required|exists:leave_types,id',
            'accrual_frequency' => 'required|in:monthly,yearly,per_pay_period',
            'accrual_rate' => 'required|numeric|min:0',
            'max_accrual_days' => 'nullable|integer|min:0',
            'waiting_period_days' => 'required|integer|min:0',
            'prorate_on_join' => 'boolean',
            'allow_carryover' => 'boolean',
            'max_carryover_days' => 'nullable|integer|min:0',
            'carryover_expiry_date' => 'nullable|date',
            'allow_negative_balance' => 'boolean',
            'max_negative_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        LeaveAccrualPlan::create($validated);

        return redirect()->route('leave.config.accrual-plans.index')
            ->with('success', __('Accrual Plan created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $accrualPlan = LeaveAccrualPlan::findOrFail($id);
        $pageTitle = __('Edit Accrual Plan');
        $leaveTypes = LeaveType::all();
        return view('leave.configuration.accrual-plans.edit', compact('accrualPlan', 'pageTitle', 'leaveTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accrualPlan = LeaveAccrualPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'leave_type_id' => 'required|exists:leave_types,id',
            'accrual_frequency' => 'required|in:monthly,yearly,per_pay_period',
            'accrual_rate' => 'required|numeric|min:0',
            'max_accrual_days' => 'nullable|integer|min:0',
            'waiting_period_days' => 'required|integer|min:0',
            'prorate_on_join' => 'boolean',
            'allow_carryover' => 'boolean',
            'max_carryover_days' => 'nullable|integer|min:0',
            'carryover_expiry_date' => 'nullable|date',
            'allow_negative_balance' => 'boolean',
            'max_negative_days' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $accrualPlan->update($validated);

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

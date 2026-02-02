<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\LeaveAccrualPlan;
use Illuminate\Http\Request;

class TimeOffTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveType::query();

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('type_name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }
        
        // Filters
        if ($request->has('filter')) {
            if ($request->filter == 'paid') {
                $query->where('is_paid', true);
            } elseif ($request->filter == 'unpaid') {
                $query->where('is_paid', false);
            }
        }

        $leaveTypes = $query->orderBy('sort_order')->orderBy('type_name')->get();
        $pageTitle = __('Time Off Types');

        // Search Config
        $searchConfig = [
            'action' => route('leave.config.time-off-types.index'),
            'fields' => [
                ['key' => 'search', 'label' => 'Name or Description'],
            ],
            'filters' => [
                ['label' => 'Paid Leaves', 'value' => 'paid'],
                ['label' => 'Unpaid Leaves', 'value' => 'unpaid'],
            ],
        ];

        return view('leave.configuration.time-off-types.index', compact('leaveTypes', 'pageTitle', 'searchConfig'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = __('Create Time Off Type');
        $accrualPlans = LeaveAccrualPlan::active()->get();
        return view('leave.configuration.time-off-types.create', compact('pageTitle', 'accrualPlans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:255',
            'is_paid' => 'boolean',
            'max_date_allowed' => 'required|integer|min:0',
            'color' => 'nullable|string|max:7',
            'uses_accrual' => 'boolean',
            'default_accrual_plan_id' => 'nullable|exists:leave_accrual_plans,id',
            'requires_approval' => 'boolean',
            'approval_levels' => 'integer|min:0',
            'description' => 'nullable|string',
        ]);

        LeaveType::create($validated);

        return redirect()->route('leave.config.time-off-types.index')
            ->with('success', __('Time Off Type created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $leaveType = LeaveType::findOrFail($id);
        $pageTitle = __('Edit Time Off Type');
        $accrualPlans = LeaveAccrualPlan::active()->get();
        return view('leave.configuration.time-off-types.edit', compact('leaveType', 'pageTitle', 'accrualPlans'));
    }

    public function update(Request $request, string $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'type_name' => 'required|string|max:255',
            'is_paid' => 'boolean',
            'max_date_allowed' => 'required|integer|min:0',
            'color' => 'nullable|string|max:7',
            'uses_accrual' => 'boolean',
            'default_accrual_plan_id' => 'nullable|exists:leave_accrual_plans,id',
            'requires_approval' => 'boolean',
            'approval_levels' => 'integer|min:0',
            'description' => 'nullable|string',
        ]);

        $leaveType->update($validated);

        return redirect()->route('leave.config.time-off-types.index')
            ->with('success', __('Time Off Type updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $leaveType = LeaveType::findOrFail($id);
        $leaveType->delete();

        return redirect()->route('leave.config.time-off-types.index')
            ->with('success', __('Time Off Type deleted successfully.'));
    }
}

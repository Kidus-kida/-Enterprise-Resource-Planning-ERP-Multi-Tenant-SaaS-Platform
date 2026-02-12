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
            $query->where(function ($q) use ($term) {
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
            'description' => 'nullable|string',

            // Time Off Logic
            'duration_type' => 'required|in:day,half_day,hours',
            'count_as' => 'required|in:absence,worked_time',
            'leave_allowed_interval' => 'nullable|string',

            // Availability & Visibility
            'max_date_allowed' => 'required|integer|min:0',
            'ignore_public_holidays' => 'boolean',
            'hide_on_dashboard' => 'boolean',
            'eligible_for_accrual' => 'boolean',

            // Notification
            'notify_hr' => 'boolean',
            'hr_notification_recipients' => 'nullable|array',

            // Allocation Requests
            'requires_allocation' => 'boolean',
            'employee_requests_allowed' => 'boolean',
            'allocation_approval_levels' => 'integer|min:1|max:3',

            // Leave Behavior (Requests)
            'requires_attachment' => 'boolean',
            'min_days_notice' => 'integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'allow_half_day' => 'boolean',
            'is_paid' => 'boolean',

            // Request Approval Settings
            'requires_approval' => 'boolean',
            'approval_levels' => 'integer|min:1|max:3',
            'auto_approve_if_balance' => 'boolean',

            // Balance Settings
            'allow_negative_balance' => 'boolean',
            'max_negative_balance' => 'integer|min:0',
            'can_carry_forward' => 'boolean',
            'max_carry_forward' => 'integer|min:0',
            'carry_forward_expiry' => 'nullable|integer|min:1|max:12',

            'color' => 'nullable|string|max:7',
            'default_accrual_plan_id' => 'nullable|exists:leave_accrual_plans,id',
        ]);

        // Default Defaults
        $defaults = [
            'notify_hr' => false,
            'ignore_public_holidays' => false,
            'hide_on_dashboard' => false,
            'eligible_for_accrual' => false,
            'requires_allocation' => true,
            'employee_requests_allowed' => false,
            'requires_attachment' => false,
            'allow_half_day' => true,
            'is_paid' => true,
            'requires_approval' => true,
            'auto_approve_if_balance' => false,
            'allow_negative_balance' => false,
            'can_carry_forward' => false,
        ];

        $data = array_merge($defaults, $validated);

        LeaveType::create($data);

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
            'description' => 'nullable|string',

            // Time Off Logic
            'duration_type' => 'required|in:day,half_day,hours',
            'count_as' => 'required|in:absence,worked_time',
            'leave_allowed_interval' => 'nullable|string',

            // Availability & Visibility
            'max_date_allowed' => 'required|integer|min:0',
            'ignore_public_holidays' => 'boolean',
            'hide_on_dashboard' => 'boolean',
            'eligible_for_accrual' => 'boolean',

            // Notification
            'notify_hr' => 'boolean',
            'hr_notification_recipients' => 'nullable|array',

            // Allocation Requests
            'requires_allocation' => 'boolean',
            'employee_requests_allowed' => 'boolean',
            'allocation_approval_levels' => 'integer|min:1|max:3',

            // Leave Behavior (Requests)
            'requires_attachment' => 'boolean',
            'min_days_notice' => 'integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'allow_half_day' => 'boolean',
            'is_paid' => 'boolean',

            // Request Approval Settings
            'requires_approval' => 'boolean',
            'approval_levels' => 'integer|min:1|max:3',
            'auto_approve_if_balance' => 'boolean',

            // Balance Settings
            'allow_negative_balance' => 'boolean',
            'max_negative_balance' => 'integer|min:0',
            'can_carry_forward' => 'boolean',
            'max_carry_forward' => 'integer|min:0',
            'carry_forward_expiry' => 'nullable|integer|min:1|max:12',

            'color' => 'nullable|string|max:7',
            'default_accrual_plan_id' => 'nullable|exists:leave_accrual_plans,id',
        ]);

        // Default Defaults for Boolean/Checkbox fields if missing
        $defaults = [
            'notify_hr' => false,
            'ignore_public_holidays' => false,
            'hide_on_dashboard' => false,
            'eligible_for_accrual' => false,
            'requires_allocation' => true,
            'employee_requests_allowed' => false,
            'requires_attachment' => false,
            'allow_half_day' => true,
            'is_paid' => true,
            'requires_approval' => true,
            'auto_approve_if_balance' => false,
            'allow_negative_balance' => false,
            'can_carry_forward' => false,
        ];

        // Merge defaults with validated data
        // Note: For update, we must be careful. 
        // checkboxes not sent means false. 
        // validating checkboxes as 'boolean' usually handles this if present, 
        // but if missing from request, we need to explicitly set them to false.

        foreach ($defaults as $key => $value) {
            if (!isset($validated[$key])) {
                $validated[$key] = $value;
            }
        }

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

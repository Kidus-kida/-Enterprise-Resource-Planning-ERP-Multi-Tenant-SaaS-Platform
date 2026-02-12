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
        
        // Get users and roles for HR notification recipients
        $users = \App\Models\User::where('is_active', 1)
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();
        $roles = \Spatie\Permission\Models\Role::all();
        
        return view('leave.configuration.time-off-types.create', compact('pageTitle', 'accrualPlans', 'users', 'roles'));
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

            
            // Notification
            'hr_notification_recipients' => 'nullable|array',

            // Allocation Requests
            'allocation_approval_levels' => 'integer|min:1|max:3',

            // Leave Behavior (Requests)
            'min_days_notice' => 'integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            
            // Request Approval Settings
            'approval_levels' => 'integer|min:1|max:3',
            
            // Balance Settings
            'max_negative_balance' => 'integer|min:0',
            'max_carry_forward' => 'integer|min:0',
            'carry_forward_expiry' => 'nullable|integer|min:1|max:12',

            'color' => 'nullable|string|max:7',
        ]);

        // Handing Boolean Fields explicitly since unchecked boxes aren't sent
        $validated['ignore_public_holidays'] = $request->boolean('ignore_public_holidays');
        $validated['hide_on_dashboard'] = $request->boolean('hide_on_dashboard');
        $validated['eligible_for_accrual'] = $request->boolean('eligible_for_accrual');
        $validated['notify_hr'] = $request->boolean('notify_hr');
        $validated['requires_allocation'] = $request->boolean('requires_allocation');
        $validated['employee_requests_allowed'] = $request->boolean('employee_requests_allowed');
        $validated['requires_attachment'] = $request->boolean('requires_attachment');
        $validated['allow_half_day'] = $request->boolean('allow_half_day');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['auto_approve_if_balance'] = $request->boolean('auto_approve_if_balance');
        $validated['allow_negative_balance'] = $request->boolean('allow_negative_balance');
        $validated['can_carry_forward'] = $request->boolean('can_carry_forward');

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
        
        // Get users and roles for HR notification recipients
        $users = \App\Models\User::where('is_active', 1)
            ->select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->get();
        $roles = \Spatie\Permission\Models\Role::all();
        
        return view('leave.configuration.time-off-types.edit', compact('leaveType', 'pageTitle', 'accrualPlans', 'users', 'roles'));
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

            
            // Notification
            'hr_notification_recipients' => 'nullable|array',

            // Allocation Requests
            'allocation_approval_levels' => 'integer|min:1|max:3',

            // Leave Behavior (Requests)
            'min_days_notice' => 'integer|min:0',
            'max_consecutive_days' => 'nullable|integer|min:1',
            
            // Request Approval Settings
            'approval_levels' => 'integer|min:1|max:3',
            
            // Balance Settings
            'max_negative_balance' => 'integer|min:0',
            'max_carry_forward' => 'integer|min:0',
            'carry_forward_expiry' => 'nullable|integer|min:1|max:12',

            'color' => 'nullable|string|max:7',
        ]);

        // Handing Boolean Fields explicitly since unchecked boxes aren't sent
        $validated['ignore_public_holidays'] = $request->boolean('ignore_public_holidays');
        $validated['hide_on_dashboard'] = $request->boolean('hide_on_dashboard');
        $validated['eligible_for_accrual'] = $request->boolean('eligible_for_accrual');
        $validated['notify_hr'] = $request->boolean('notify_hr');
        $validated['requires_allocation'] = $request->boolean('requires_allocation');
        $validated['employee_requests_allowed'] = $request->boolean('employee_requests_allowed');
        $validated['requires_attachment'] = $request->boolean('requires_attachment');
        $validated['allow_half_day'] = $request->boolean('allow_half_day');
        $validated['is_paid'] = $request->boolean('is_paid');
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['auto_approve_if_balance'] = $request->boolean('auto_approve_if_balance');
        $validated['allow_negative_balance'] = $request->boolean('allow_negative_balance');
        $validated['can_carry_forward'] = $request->boolean('can_carry_forward');

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

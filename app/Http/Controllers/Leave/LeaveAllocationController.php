<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\LeaveAccrualPlan;
use Carbon\Carbon;

use Illuminate\Validation\Rule;

class LeaveAllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LeaveAllocation::with(['user', 'leaveType']);

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->whereHas('user', function($q) use ($term) {
                $q->where('firstname', 'like', "%{$term}%")
                  ->orWhere('lastname', 'like', "%{$term}%");
            })->orWhereHas('leaveType', function($q) use ($term) {
                $q->where('type_name', 'like', "%{$term}%");
            });
        }

        // Filters
        if ($request->has('filter')) {
            if ($request->filter == 'current_year') {
                $query->whereYear('period_start', now()->year);
            } elseif ($request->filter == 'next_year') {
                $query->whereYear('period_start', now()->year + 1);
            } elseif ($request->filter == 'pending') {
                $query->where('status', 'pending');
            }
        }

        $allocations = $query->orderBy('id', 'desc')->paginate(15);
            
        $pageTitle = __('Leave Allocations');
        
        $searchConfig = [
            'action' => route('leave.management.allocations.index'),
            'fields' => [
                ['key' => 'search', 'label' => 'Employee or Leave Type'],
            ],
            'filters' => [
                ['label' => 'Current Year (' . now()->year . ')', 'value' => 'current_year'],
                ['label' => 'Next Year', 'value' => 'next_year'],
                ['label' => 'Pending Requests', 'value' => 'pending'],
                ['label' => 'Approved', 'value' => 'approved'],
            ],
        ];

        return view('leave.management.allocations.index', compact('allocations', 'pageTitle', 'searchConfig'));
    }

    public function create()
    {
        $pageTitle = __('Allocate Leave (Manual)');
        $users = User::all(); // Should filter active
        $leaveTypes = LeaveType::all();
        $accrualPlans = LeaveAccrualPlan::active()->get();
        return view('leave.management.allocations.create', compact('pageTitle', 'users', 'leaveTypes', 'accrualPlans'));
    }

    // New: Form for Employees to Request Allocation
    public function request()
    {
        $pageTitle = __('Request New Allocation');
        $leaveTypes = LeaveType::where('is_active', true)->get(); 
        // Filter types that allow requests if such a flag existed, currently all active
        return view('leave.management.allocations.request', compact('pageTitle', 'leaveTypes'));
    }
    
    // New: Store Employee Request
    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => ['required', Rule::exists(LeaveType::class, 'id')],
            'allocated_days' => 'required|numeric|min:0.5',
            'notes' => 'required|string|min:5',
            'start_date' => 'required|date', // Changed from year
        ]);

        // Prevent Duplicate Allocation Check (Simplified since unique constraint removed)
        $existingAllocation = LeaveAllocation::where('user_id', Auth::id())
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('period_start', $validated['start_date'])
            ->first();

        if ($existingAllocation) {
             return back()->with('error', __('You already have an allocation request starting on :date. Status: :status', [
                'date' => $validated['start_date'],
                'status' => $existingAllocation->status
            ]));
        }

        LeaveAllocation::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'allocated_days' => $validated['allocated_days'],
            'period_start' => $validated['start_date'],
            'period_end' => null, // Default to no limit for requests
            'notes' => $validated['notes'],
            'allocation_type' => 'manual', // or 'request'
            'status' => 'pending',
            'available_days' => 0, // Not available until approved
            'used_days' => 0,
            'allocated_by' => null, // Self-requested
        ]);

        return redirect()->route('leave.my-time')->with('success', __('Allocation request submitted for approval.'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', Rule::exists(User::class, 'id')],
            'leave_type_id' => ['required', Rule::exists(LeaveType::class, 'id')],
            'allocated_days' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'allocation_type' => 'required|in:manual,accrual',
            'accrual_plan_id' => [
                'required_if:allocation_type,accrual',
                'nullable',
                Rule::exists(LeaveAccrualPlan::class, 'id')
            ],
            'run_until_option' => 'required|in:no_limit,date',
            'run_until_date' => 'required_if:run_until_option,date|nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        // Check duplicate: Only warn if exact start date match for same user/type
        $existingAllocation = LeaveAllocation::where('user_id', $validated['user_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('period_start', $validated['start_date'])
            ->first();

        if ($existingAllocation) {
            return back()->withInput()->with('error', __(
                'An allocation already exists for this user and leave type starting on :date.',
                ['date' => $validated['start_date']]
            ));
        }

        $data = [
            'user_id' => $validated['user_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'allocated_days' => $validated['allocated_days'],
            'notes' => $validated['notes'] ?? null,
            'allocation_type' => $validated['allocation_type'],
            'is_manual_allocation' => $validated['allocation_type'] === 'manual',
            'accrual_plan_id' => $validated['allocation_type'] === 'accrual' ? $validated['accrual_plan_id'] : null,
            'period_start' => $validated['start_date'],
            'period_end' => $validated['run_until_option'] === 'date' ? $validated['run_until_date'] : null,
            'opening_balance' => 0,
            'available_days' => $validated['allocated_days'],
            'allocated_by' => Auth::id(),
            'status' => 'approved',
        ];

        LeaveAllocation::create($data);

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Leave allocated successfully.'));
    }

    public function edit(string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);
        $pageTitle = __('Edit Allocation');
        $accrualPlans = LeaveAccrualPlan::active()->get();
        return view('leave.management.allocations.edit', compact('allocation', 'pageTitle', 'accrualPlans'));
    }

    public function update(Request $request, string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);

        if ($request->has('status_action')) {
            // Approval Logic
            $action = $request->status_action; // 'approve' or 'reject'
            
            if ($action === 'approve') {
                $allocation->status = 'approved';
                $allocation->available_days = $allocation->allocated_days; // Make days available
                $allocation->allocated_by = Auth::id(); // Approver
                $allocation->save();
                return back()->with('success', __('Allocation Approved. Days are now available to employee.'));
            } elseif ($action === 'reject') {
                $allocation->status = 'rejected';
                $allocation->available_days = 0;
                $allocation->save();
                return back()->with('success', __('Allocation Rejected.'));
            }
        }

        // Standard Update
        $validated = $request->validate([
            'allocated_days' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'allocation_type' => 'required|in:manual,accrual',
            'accrual_plan_id' => [
                'required_if:allocation_type,accrual',
                'nullable',
                Rule::exists(LeaveAccrualPlan::class, 'id')
            ],
            'run_until_option' => 'required|in:no_limit,date',
            'run_until_date' => 'required_if:run_until_option,date|nullable|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        // Only adjust balance if it was already approved
        if ($allocation->status === 'approved') {
            $diff = $validated['allocated_days'] - $allocation->allocated_days;
            $allocation->available_days += $diff; 
        } else {
            // If pending, just update the requested amount
            $allocation->available_days = 0;
        }
        
        $allocation->allocated_days = $validated['allocated_days'];
        $allocation->notes = $validated['notes'];
        $allocation->period_start = $validated['start_date'];
        $allocation->period_end = $validated['run_until_option'] === 'date' ? $validated['run_until_date'] : null;
        $allocation->allocation_type = $validated['allocation_type'];
        $allocation->is_manual_allocation = $validated['allocation_type'] === 'manual';
        $allocation->accrual_plan_id = $validated['allocation_type'] === 'accrual' ? $validated['accrual_plan_id'] : null;

        $allocation->save();

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Allocation updated successfully.'));
    }

    public function destroy(string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);
        $allocation->delete();

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Allocation deleted successfully.'));
    }
}

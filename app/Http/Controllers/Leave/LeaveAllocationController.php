<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                $query->where('year', now()->year);
            } elseif ($request->filter == 'next_year') {
                $query->where('year', now()->year + 1);
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
        return view('leave.management.allocations.create', compact('pageTitle', 'users', 'leaveTypes'));
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
            'leave_type_id' => 'required|exists:leave_types,id',
            'allocated_days' => 'required|numeric|min:0.5',
            'notes' => 'required|string|min:5',
            'year' => 'nullable|integer', // Defaults to current if not set
        ]);

        // Prevent Duplicate Allocation (database has unique constraint on user_id, leave_type_id, year)
    $year = $validated['year'] ?? now()->year;
    $existingAllocation = LeaveAllocation::where('user_id', Auth::id())
        ->where('leave_type_id', $validated['leave_type_id'])
        ->where('year', $year)
        ->first();

    if ($existingAllocation) {
        if ($existingAllocation->status === 'pending') {
            return back()->with('error', __('You already have a pending allocation request for this leave type in :year.', ['year' => $year]));
        } elseif ($existingAllocation->status === 'approved') {
            return back()->with('error', __('You already have an approved allocation for this leave type in :year. Current allocation: :days days.', [
                'year' => $year,
                'days' => $existingAllocation->allocated_days
            ]));
        } elseif ($existingAllocation->status === 'rejected') {
            return back()->with('error', __('Your previous allocation request for this leave type in :year was rejected. Reason: :reason', [
                'year' => $year,
                'reason' => $existingAllocation->rejection_reason ?? 'Not specified'
            ]));
        }
    }    

        LeaveAllocation::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'allocated_days' => $validated['allocated_days'],
            'year' => $validated['year'] ?? now()->year,
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
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'allocated_days' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2020|max:2030', // Adjust range as needed
            'notes' => 'nullable|string',
        ]);

        // Check if allocation already exists for this user/type/year
    $existingAllocation = LeaveAllocation::where('user_id', $validated['user_id'])
        ->where('leave_type_id', $validated['leave_type_id'])
        ->where('year', $validated['year'])
        ->first();

    if ($existingAllocation) {
        return back()->withInput()->with('error', __(
            'An allocation already exists for this user, leave type, and year. Status: :status, Allocated Days: :days',
            [
                'status' => ucfirst($existingAllocation->status),
                'days' => $existingAllocation->allocated_days
            ]
        ));
    }

    $validated['opening_balance'] = 0; 
    $validated['available_days'] = $validated['allocated_days']; 
    $validated['is_manual_allocation'] = true;
    $validated['allocated_by'] = Auth::id();
    $validated['status'] = 'approved'; // Admin created = auto approved

        LeaveAllocation::create($validated);

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Leave allocated successfully.'));
    }

    public function edit(string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);
        $pageTitle = __('Edit Allocation');
        return view('leave.management.allocations.edit', compact('allocation', 'pageTitle'));
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

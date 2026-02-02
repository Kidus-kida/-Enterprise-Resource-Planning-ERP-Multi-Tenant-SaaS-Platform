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
            ],
        ];

        return view('leave.management.allocations.index', compact('allocations', 'pageTitle', 'searchConfig'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = __('Allocate Leave');
        $users = User::all(); // Should filter active
        $leaveTypes = LeaveType::all();
        return view('leave.management.allocations.create', compact('pageTitle', 'users', 'leaveTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
        $exists = LeaveAllocation::where('user_id', $validated['user_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return back()->with('error', __('Allocation already exists for this user and leave type in the selected year. Please edit the existing allocation instead.'));
        }

        $validated['opening_balance'] = 0; // Or allow setting?
        $validated['available_days'] = $validated['allocated_days']; 
        $validated['is_manual_allocation'] = true;
        $validated['allocated_by'] = Auth::id();

        LeaveAllocation::create($validated);

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Leave allocated successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);
        $pageTitle = __('Edit Allocation');
        return view('leave.management.allocations.edit', compact('allocation', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);

        $validated = $request->validate([
            'allocated_days' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $diff = $validated['allocated_days'] - $allocation->allocated_days;
        
        $allocation->allocated_days = $validated['allocated_days'];
        $allocation->notes = $validated['notes'];
        
        // Update available balance based on difference
        // This is a simplified logic; usually services handle this
        $allocation->available_days += $diff; 
        
        $allocation->save();

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Allocation updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $allocation = LeaveAllocation::findOrFail($id);
        $allocation->delete();

        return redirect()->route('leave.management.allocations.index')
            ->with('success', __('Allocation deleted successfully.'));
    }
}

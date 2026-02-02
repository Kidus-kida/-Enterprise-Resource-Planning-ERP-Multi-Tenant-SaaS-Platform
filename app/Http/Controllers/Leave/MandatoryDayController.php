<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\MandatoryDay;
use App\Models\Department; // Assumes these exist
use App\Models\Designation; // Assumes these exist
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MandatoryDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MandatoryDay::query();

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
            if ($request->filter == 'current_year') {
                $query->whereYear('date', now()->year);
            } elseif ($request->filter == 'active') {
                $query->where('is_active', true);
            }
        }

        $mandatoryDays = $query->orderBy('date', 'desc')->get();
        $pageTitle = __('Mandatory Days');
        
        $searchConfig = [
            'action' => route('leave.config.mandatory-days.index'),
            'fields' => [
                ['key' => 'search', 'label' => 'Name or Description'],
            ],
            'filters' => [
                ['label' => 'Current Year', 'value' => 'current_year'],
                ['label' => 'Active Only', 'value' => 'active'],
            ],
        ];

        return view('leave.configuration.mandatory-days.index', compact('mandatoryDays', 'pageTitle', 'searchConfig'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = __('Create Mandatory Day');
        // We'll fetch departments and designations to populate dropdowns
        $departments = \App\Models\Department::all(); 
        $designations = \App\Models\Designation::all();
        return view('leave.configuration.mandatory-days.create', compact('pageTitle', 'departments', 'designations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:mandatory_days,date',
            'restriction_type' => 'required|in:no_leave,requires_approval,warning_only',
            'restriction_message' => 'nullable|string',
            'applicable_departments' => 'nullable|array',
            'applicable_designations' => 'nullable|array',
            'excluded_users' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        MandatoryDay::create($validated);

        return redirect()->route('leave.config.mandatory-days.index')
            ->with('success', __('Mandatory Day created successfully.'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mandatoryDay = MandatoryDay::findOrFail($id);
        $pageTitle = __('Edit Mandatory Day');
        $departments = \App\Models\Department::all(); 
        $designations = \App\Models\Designation::all();
        return view('leave.configuration.mandatory-days.edit', compact('mandatoryDay', 'pageTitle', 'departments', 'designations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $mandatoryDay = MandatoryDay::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:mandatory_days,date,' . $id,
            'restriction_type' => 'required|in:no_leave,requires_approval,warning_only',
            'restriction_message' => 'nullable|string',
            'applicable_departments' => 'nullable|array',
            'applicable_designations' => 'nullable|array',
            'excluded_users' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $mandatoryDay->update($validated);

        return redirect()->route('leave.config.mandatory-days.index')
            ->with('success', __('Mandatory Day updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mandatoryDay = MandatoryDay::findOrFail($id);
        $mandatoryDay->delete();

        return redirect()->route('leave.config.mandatory-days.index')
            ->with('success', __('Mandatory Day deleted successfully.'));
    }
}

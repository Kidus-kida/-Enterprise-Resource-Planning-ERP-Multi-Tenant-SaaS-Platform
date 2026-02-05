<?php

namespace App\Http\Controllers\Admin;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\DepartmentDataTable;
use App\Http\Controllers\BaseController;

class DepartmentsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = __('Departments');
        
        $query = Department::withCount('employeeDetails')
            ->with(['manager', 'parent']);

        // Filtering
        if ($request->has('filter') && $request->filter == 'archived') {
             $query->where('is_active', false);
        } else {
             $query->where('is_active', true);
        }

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%")
                  ->orWhere('company_name', 'like', "%{$term}%");
            });
        }
        
        // Specific Field Searches from Odoo Search Bar
        if ($request->has('name')) {
            $term = $request->name;
            $query->where('name', 'like', "%{$term}%");
        }
        
        if ($request->has('location')) {
            $term = $request->location;
            $query->where(function($q) use ($term) {
                $q->where('location', 'like', "%{$term}%")
                  ->orWhere('company_name', 'like', "%{$term}%");
            });
        }

        $departments = $query->get();
        $isGrouped = false;

        // Grouping
        if ($request->has('group_by')) {
            $isGrouped = true;
            $groupBy = $request->group_by;
            
            if ($groupBy == 'manager') {
                $departments = $departments->groupBy(function($item) {
                    return $item->manager ? $item->manager->fullname : 'Undefined';
                });
            } elseif ($groupBy == 'location') { // Company/Location
                $departments = $departments->groupBy(function($item) {
                     return $item->company_name ?? $item->location ?? 'Undefined';
                });
            } elseif ($groupBy == 'parent') {
                $departments = $departments->groupBy(function($item) {
                    return $item->parent ? $item->parent->name : 'Undefined';
                });
            } else {
                $isGrouped = false; // Invalid group by
            }
        }

        return view('pages.departments.index', compact('departments', 'pageTitle', 'isGrouped'));
    }


    public function create(){
        $employees = \App\Models\User::where('type', \App\Enums\UserType::EMPLOYEE)->get();
        // Fetch all departments and compute hierarchical name
        $departments = Department::with('parent')->get()->map(function($dept) {
            $dept->hierarchical_name = $this->getHierarchicalName($dept);
            return $dept;
        })->sortBy('hierarchical_name');
        $companies = cache()->remember('companies.all', 3600, fn() => \App\Company::all());
        
        return view('pages.departments.create', compact('employees', 'departments', 'companies'));
    }

    private function getHierarchicalName($department) {
        if ($department->parent) {
            return $this->getHierarchicalName($department->parent) . ' / ' . $department->name;
        }
        return $department->name;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|max: 255',
            'manager_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:departments,id',
            'color' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        Department::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'manager_id' => $request->manager_id,
            'color' => $request->color,
            'company_id' => $request->company_id,
            'description' => $request->description
        ]);
        $notification = notify('Department has been added');
        return redirect()->route('departments.index')->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function edit(Department $department)
    {
        $employees = \App\Models\User::where('type', \App\Enums\UserType::EMPLOYEE)->get();
        // Exclude self and children from parent options to prevent cycles
        $departments = Department::where('id', '!=', $department->id)
            ->with('parent') // Eager load parent
            ->get()
            ->map(function($dept) {
                $dept->hierarchical_name = $this->getHierarchicalName($dept);
                return $dept;
            })
            ->sortBy('hierarchical_name');
        $companies = cache()->remember('companies.all', 3600, fn() => \App\Company::all());
            
        return view('pages.departments.edit',compact(
            'department',
            'employees',
            'departments',
            'companies'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:departments,id',
            'color' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        
        // Prevent setting parent to self
        if ($request->parent_id == $department->id) {
             return back()->withErrors(['parent_id' => 'Department cannot be its own parent.']);
        }

        $department->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'manager_id' => $request->manager_id,
            'color' => $request->color,
            'company_id' => $request->company_id,
            'description' => $request->description,
        ]);
        $notification = notify(__("Department has been updated"));
        return redirect()->route('departments.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        $notification = notify(__('Department has been deleted'));
        return redirect()->route('departments.index')->with($notification);
    }
}

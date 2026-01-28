<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\EmployeeDataTable;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\User;
use Chatify\Facades\ChatifyMessenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use App\Company;
use App\Models\JobPosition;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = __("Employees");
        $query = User::where('type', UserType::EMPLOYEE)
            ->with(['employeeDetail.department', 'employeeDetail.designation']); // Eager load for grouping/display
        
        // Filtering
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'archived':
                     $query->where('is_active', false);
                     break;
                case 'my_department':
                    $query->whereHas('employeeDetail', function($q) {
                        $q->where('department_id', auth()->user()->employeeDetail->department_id ?? null);
                    })->where('is_active', true);
                    break;
                case 'newly_hired':
                    $query->whereHas('employeeDetail', function($q) {
                        $q->where('date_joined', '>=', now()->subDays(30)); 
                    })->where('is_active', true);
                    break;
                default:
                    $query->where('is_active', true);
                    break;
            }
        } else {
             $query->where('is_active', true);
        }

        // Search
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('firstname', 'like', "%{$term}%")
                  ->orWhere('lastname', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }
        
        if ($request->has('name')) {
            $term = $request->name;
            $query->where(function($q) use ($term) {
                $q->where('firstname', 'like', "%{$term}%")
                  ->orWhere('lastname', 'like', "%{$term}%");
            });
        }
        
        if ($request->has('email')) {
             $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->has('phone')) {
             $query->where('phone', 'like', "%{$request->phone}%");
        }
        
        // Existing department filter
        if ($request->has('department_id')) {
            $query->whereHas('employeeDetail', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        
        $employees = $query->get();
        $isGrouped = false;

        // Grouping
        if ($request->has('group_by')) {
            $isGrouped = true;
            $groupBy = $request->group_by;
            
            if ($groupBy == 'department') {
                $employees = $employees->groupBy(function($item) {
                    return $item->employeeDetail && $item->employeeDetail->department 
                        ? $item->employeeDetail->department->name 
                        : 'No Department';
                });
            } elseif ($groupBy == 'designation') {
                $employees = $employees->groupBy(function($item) {
                    return $item->employeeDetail && $item->employeeDetail->designation 
                        ? $item->employeeDetail->designation->name 
                        : 'No Designation';
                });
            } else {
                $isGrouped = false;
            }
        }
        
        // Data for Add Employee Modal
        $departments = cache()->remember('departments.all', 3600, fn() => Department::all());
        $designations = cache()->remember('designations.all', 3600, fn() => Designation::all());
        $companies = cache()->remember('companies.all', 3600, fn() => Company::all());
        $jobPositions = JobPosition::all();
        $managers = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->get();

        return view('pages.employees.index', compact(
            'pageTitle',
            'employees',
            'isGrouped',
            'departments',
            'designations',
            'companies',
            'jobPositions',
            'managers'
        ));
    }

    /**
     * Display a listing of the resource.
     */
    public function list(EmployeeDataTable $dataTable)
    {
        $pageTitle = __("employees");
        return $dataTable->render('pages.employees.list', compact(
            'pageTitle',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Cache departments and designations as they rarely change
        $departments = cache()->remember('departments.all', 3600, fn() => Department::all());
        $designations = cache()->remember('designations.all', 3600, fn() => Designation::all());
        $companies = cache()->remember('companies.all', 3600, fn() => Company::all());
        $jobPositions = JobPosition::all(); // Don't cache as we might add new ones frequently
        $managers = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->get();
        
        return view('pages.employees.create', compact(
            'departments',
            'designations',
            'companies',
            'jobPositions',
            'managers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'middlename' => 'nullable|string',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email,except,id',
            'password' => 'required|string|confirmed',
            'status' => 'required',
            'company' => 'nullable|exists:companies,id',
            'manager' => 'nullable|exists:users,id',
            'job_position' => 'nullable|exists:job_positions,id',
            'job_title' => 'nullable|string|max:255',
        ]);
        $imageName = null;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user = User::create([
            'type' => UserType::EMPLOYEE,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'username' => $request->username,
            'address' => $request->address,
            'country' => $request->country_name,
            'country_code' => $request->country_code,
            'dial_code' => $request->dial_code,
            'phone' => $request->phone,
            'avatar' => $imageName,
            'created_by' => auth()->user()->id,
            'is_active' => !empty($request->status),
            'password' => Hash::make($request->password)
        ]);
        if (!empty($user)) {
            $user->assignRole(UserType::EMPLOYEE);
            $totalEmployees = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->count();
            $empId = "EMP-" . pad_zeros(($totalEmployees + 1));
            EmployeeDetail::create([
                'emp_id' => $empId,
                'user_id' => $user->id,
                'department_id' => $request->department,
                'designation_id' => $request->designation,
                'company_id' => $request->company,
                'manager_id' => $request->manager,
                'job_position_id' => $request->job_position,
                'job_title' => $request->job_title,
            ]);
        }
        $notification = notify(__('Employee has been added'));
        return back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $employee)
    {
        $id = Crypt::decrypt($employee);
        // Eager load all relationships needed for the profile page
        $user = User::with([
            'employeeDetail.designation',
            'employeeDetail.department',
            'employeeDetail.salaryDetails',
            'employeeDetail.allowances',
            'employeeDetail.deductions',
            'employeeDetail.education',
            'employeeDetail.workExperience',
            'family',
            'assets',
            'attendances'
        ])->findOrFail($id);
        $employee = $user->employeeDetail;
        $pageTitle = __('Employee Profile');
        return view('pages.employees.show', compact(
            'employee',
            'user',
            'pageTitle'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $employee)
    {
        $userId = Crypt::decrypt($employee);
        // Eager load employee details for the edit form
        $employee = User::with('employeeDetail')->findOrFail($userId);
        // Cache departments and designations as they rarely change
        $departments = cache()->remember('departments.all', 3600, fn() => Department::all());
        $designations = cache()->remember('designations.all', 3600, fn() => Designation::all());
        return view('pages.employees.edit', compact(
            'departments',
            'designations',
            'employee'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $employee)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'nullable|string|confirmed',
            'status' => 'required',
        ]);
        $user = $employee;
        $imageName = $user->avatar;
        if ($request->hasFile('avatar')) {
            $imageName = time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('storage/users'), $imageName);
        }
        $user->update([
            'firstname' => $request->firstname ?? $user->firstname,
            'middlename' => $request->middlename ?? $user->middlename,
            'lastname' => $request->lastname ?? $user->lastname,
            'email' => $request->email ?? $user->email,
            'username' => $request->username ?? $user->username,
            'address' => $request->address ?? $user->address,
            'country' => $request->country_name ?? $user->country,
            'country_code' => $request->country_code ?? $user->country_code,
            'dial_code' => $request->dial_code ?? $user->dial_code,
            'phone' => $request->phone ?? $user->phone,
            'avatar' => $imageName,
            'is_active' => !empty($request->status) ?? $user->is_active,
            'password' => !empty($request->password) ? Hash::make($request->password) : $user->password
        ]);
        if (!empty($user)) {
            if(!$user->hasRole(UserType::EMPLOYEE)){
                $user->assignRole(UserType::EMPLOYEE);
            }
            $employeeDetails = $user->employeeDetail;
            if (!empty($employeeDetails) && empty($employeeDetails->emp_id)) {
                $totalEmployees = User::where('type', UserType::EMPLOYEE)->where('is_active', true)->count();
                $empId = "EMP-" . pad_zeros(($totalEmployees + 1));
            }
            EmployeeDetail::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'emp_id' => $empId ?? $employee->emp_id,
                'user_id' => $user->id,
                'department_id' => $request->department,
                'designation_id' => $request->designation,
            ]);
        }
        $notification = notify(__("Employee has been updated"));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee)
    {
        $employee->delete();
        $notification = notify(__("Employee has been deleted"));
        return back()->with($notification);
    }
}

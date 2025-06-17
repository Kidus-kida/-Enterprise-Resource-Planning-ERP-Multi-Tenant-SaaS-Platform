<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserType;
use App\Models\Performance;

class EvaluationController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:view-evaluation')->only('index');
        $this->middleware('permission:create-evaluation')->only(['showEvaluationForm', 'submitEvaluation']);
        $this->middleware('permission:edit-evaluation')->only(['showEvaluationForm', 'submitEvaluation']);
        $this->middleware('permission:delete-evaluation')->only('destroy');

        $this->middleware('permission:view-evaluation-assignment')->only('assignEvaluatorView');
        $this->middleware('permission:create-evaluation-assignment')->only('assignEvaluator');
        $this->middleware('permission:edit-evaluation-assignment')->only('assignEvaluator');
        $this->middleware('permission:delete-evaluation-assignment')->only('destroy');
    }
    
    public function index()
    {
        $assignedEmployees = auth()->user()->evaluatees()->with(['employeeDetail.department', 'employeeDetail.designation'])->get();
        return view('pages.evaluation.index', compact('assignedEmployees'));
    }

    public function assignEvaluatorView()
    {
        $employees = User::where('type', UserType::EMPLOYEE)->with(['employeeDetail.department', 'employeeDetail.designation'])->get();
        $evaluations = Performance::with(['user.employeeDetail.department', 'user.employeeDetail.designation', 'user'])
            ->orderByDesc('created_at')->get();
        return view('pages.evaluation.assign-evaluator', compact('employees', 'evaluations'));
    }

    public function assignEvaluator(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'evaluators' => 'required|array',
            'evaluators.*' => 'exists:users,id',
        ]);

        $employee = User::findOrFail($request->employee_id);
        $employee->evaluators()->sync($request->evaluators);

        // Notify the evaluators
        $notification = notify(__('Evaluators Assigned Successfully'));
        return back()->with($notification);
        // return redirect()->back()->with('success', 'Evaluators Assigned Successfully');
    }

    public function showEvaluationForm($employeeId)
    {
        $employee = User::with(['employeeDetail.department', 'employeeDetail.designation'])->findOrFail($employeeId);
        // Optionally, check if the logged-in user is allowed to evaluate this employee
        if (!auth()->user()->evaluatees->contains($employee)) {
            abort(403, 'Unauthorized');
        }
        return view('pages.evaluation.form', compact('employee'));
    }

    public function submitEvaluation(Request $request, $employeeId)
    {
        $employee = User::findOrFail($employeeId);
        if (!auth()->user()->evaluatees->contains($employee)) {
            abort(403, 'Unauthorized');
        }
        $validated = $request->validate([
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after_or_equal:evaluation_period_start',
            'knowledge_of_job' => 'required|integer|min:1|max:5',
            'quality_of_work' => 'required|integer|min:1|max:5',
            'quantity_of_work' => 'required|integer|min:1|max:5',
            'emotional_intelligence' => 'required|integer|min:1|max:5',
            'time_management' => 'required|integer|min:1|max:5',
            'initiative_and_creativity' => 'required|integer|min:1|max:5',
            'team_work' => 'required|integer|min:1|max:5',
            'accountablity' => 'required|integer|min:1|max:5',
            'attendance_and_punctuality' => 'required|integer|min:1|max:5',
            'company_resource_usage_and_protection' => 'required|integer|min:1|max:5',
            'communication_skills' => 'required|integer|min:1|max:5',
        ]);
        $performance = Performance::create([
            'user_id' => $employee->id,
            'evaluator_id' => auth()->id(),
            'evaluation_period_start' => $validated['evaluation_period_start'],
            'evaluation_period_end' => $validated['evaluation_period_end'],
            'knowledge_of_job' => $validated['knowledge_of_job'],
            'quality_of_work' => $validated['quality_of_work'],
            'quantity_of_work' => $validated['quantity_of_work'],
            'emotional_intelligence' => $validated['emotional_intelligence'],
            'time_management' => $validated['time_management'],
            'initiative_and_creativity' => $validated['initiative_and_creativity'],
            'team_work' => $validated['team_work'],
            'accountablity' => $validated['accountablity'],
            'attendance_and_punctuality' => $validated['attendance_and_punctuality'],
            'company_resource_usage_and_protection' => $validated['company_resource_usage_and_protection'],
            'communication_skills' => $validated['communication_skills'],
            // Store names for reporting
            'staff_name' => $employee->full_name ?? $employee->name ?? trim(($employee->firstname ?? '') . ' ' . ($employee->middlename ?? '') . ' ' . ($employee->lastname ?? '')),
            'evaluator_name' => (auth()->user()->full_name ?? auth()->user()->name ?? trim((auth()->user()->firstname ?? '') . ' ' . (auth()->user()->middlename ?? '') . ' ' . (auth()->user()->lastname ?? ''))),
            'department' => optional($employee->employeeDetail->department)->name ?? '-',
            'job_title' => optional($employee->employeeDetail->designation)->name ?? '-',
        ]);
        // Calculate average and percentage
        $criteria = [
            'knowledge_of_job',
            'quality_of_work',
            'quantity_of_work',
            'emotional_intelligence',
            'time_management',
            'initiative_and_creativity',
            'team_work',
            'accountablity',
            'attendance_and_punctuality',
            'company_resource_usage_and_protection',
            'communication_skills',
        ];
        $total = 0;
        foreach ($criteria as $field) {
            $total += $validated[$field];
        }
        $max = count($criteria) * 5;
        $percentage = round(($total / $max) * 100, 2);
        $performance->average_score = $total / count($criteria);
        $performance->percentage_score = $percentage;
        $performance->save();

        $notification = notify(__('Evaluation submitted successfully.'));
        return back()->with($notification);

        // return redirect()->route('evaluation.index')->with('success', 'Evaluation submitted successfully.');
    }

    public function destroy(Performance $evaluation)
    {
        $evaluation->delete();
        return redirect()->back()->with('success', 'Evaluation deleted successfully.');
    }
}

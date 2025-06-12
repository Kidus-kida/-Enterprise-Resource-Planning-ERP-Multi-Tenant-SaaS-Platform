<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserType;

class EvaluationController extends Controller
{
    public function index()
    {
        return view('pages.evaluation.index');
    }

    public function assignEvaluatorView()
    {
        $employees = User::where('type', UserType::EMPLOYEE)->with(['employeeDetail.department', 'employeeDetail.designation'])->get();
        return view('pages.evaluation.assign-evaluator', compact('employees'));
    }
}

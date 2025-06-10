<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EvaluationController extends Controller
{
    public function index() {
        return view('pages.evaluation.evaluation');
    }

    public function showGuide() {
        return view('pages.evaluation.evaluation-guide', [
            'pageTitle' => __('Employee Evaluation Guide')
        ]);
    }
}

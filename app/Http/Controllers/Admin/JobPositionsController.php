<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\JobPosition;

class JobPositionsController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $jobPosition = JobPosition::create($validated);

        return response()->json([
            'success' => true,
            'job_position' => $jobPosition
        ]);
    }
}

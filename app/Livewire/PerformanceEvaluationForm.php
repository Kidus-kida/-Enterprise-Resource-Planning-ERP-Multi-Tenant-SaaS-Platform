<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Performance;
use Illuminate\Validation\Rule;

class PerformanceEvaluationForm extends Component
{
    public $staff_name, $department, $employment_date, $job_title;
    public $evaluation_period_start, $evaluation_period_end;

    public $ratings = [
        'knowledge_of_job' => null,
        'quality_of_work' => null,
        'quantity_of_work' => null,
        'emotional_intelligence' => null,
        'time_management' => null,
        'initiative_and_creativity' => null,
        'team_work' => null,
        'accountability' => null,
        'attendance' => null,
        'efficiency_in_resource_use' => null,
        'personality' => null,
        'relation_with_supervisors' => null,
    ];

    public function rules()
    {
        $rules = [
            'staff_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'employment_date' => 'required|date',
            'job_title' => 'required|string|max:255',
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after_or_equal:evaluation_period_start',
        ];

        foreach ($this->ratings as $key => $value) {
            $rules["ratings.$key"] = ['required', Rule::in([1, 2, 3, 4, 5])];
        }

        return $rules;
    }

    public function submit()
    {
        $this->validate();

        $average = collect($this->ratings)->avg();

        $result = match (true) {
            $average < 64 => ['E', 'Below Standard'],
            $average <= 74 => ['D', 'Good'],
            $average <= 85 => ['C', 'Very Good'],
            $average <= 95 => ['B', 'Excellent'],
            default => ['A', 'Outstanding'],
        };

        Performance::create(array_merge([
            'staff_name' => $this->staff_name,
            'department' => $this->department,
            'employment_date' => $this->employment_date,
            'job_title' => $this->job_title,
            'evaluation_period_start' => $this->evaluation_period_start,
            'evaluation_period_end' => $this->evaluation_period_end,
            'average_score' => $average,
            'performance_result' => $result[0],
            'rating_remark' => $result[1],
        ], $this->ratings));

        session()->flash('success', 'Performance Evaluation Submitted!');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.performance-evaluation-form');
    }
}

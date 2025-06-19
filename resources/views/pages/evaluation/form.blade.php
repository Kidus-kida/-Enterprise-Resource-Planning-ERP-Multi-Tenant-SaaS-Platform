@extends('layouts.app')

@section('page-content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 text-center">Evaluate: {{ $employee->full_name ?? $employee->name }}</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('evaluation.submit', $employee->id) }}">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Staff Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->full_name ?? $employee->name }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->employeeDetail->department->name ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" class="form-control"
                                        value="{{ $employee->employeeDetail->designation->name ?? '-' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control" value="{{ $employee->email }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Evaluation Period Start</label>
                                    <input type="date" class="form-control" name="evaluation_period_start" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Evaluation Period End</label>
                                    <input type="date" class="form-control" name="evaluation_period_end" required>
                                </div>
                            </div>
                            <hr>
                            <h5 class="mb-3">Evaluation Criteria (Rate 1-5)</h5>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Knowledge of Job</label>
                                    <input type="number" min="1" max="5" class="form-control" name="knowledge_of_job">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quality of Work</label>
                                    <input type="number" min="1" max="5" class="form-control" name="quality_of_work">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Quantity of Work</label>
                                    <input type="number" min="1" max="5" class="form-control" name="quantity_of_work">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Emotional Intelligence</label>
                                    <input type="number" min="1" max="5" class="form-control" name="emotional_intelligence">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Time Management</label>
                                    <input type="number" min="1" max="5" class="form-control" name="time_management">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Initiative and Creativity</label>
                                    <input type="number" min="1" max="5" class="form-control"
                                        name="initiative_and_creativity">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Team Work</label>
                                    <input type="number" min="1" max="5" class="form-control" name="team_work">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Accountability</label>
                                    <input type="number" min="1" max="5" class="form-control" name="accountablity">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Attendance and Punctuality</label>
                                    <input type="number" min="1" max="5" class="form-control"
                                        name="attendance_and_punctuality">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company Resource Usage & Protection</label>
                                    <input type="number" min="1" max="5" class="form-control"
                                        name="company_resource_usage_and_protection">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Communication Skills</label>
                                    <input type="number" min="1" max="5" class="form-control" name="communication_skills">
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success">Submit Evaluation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
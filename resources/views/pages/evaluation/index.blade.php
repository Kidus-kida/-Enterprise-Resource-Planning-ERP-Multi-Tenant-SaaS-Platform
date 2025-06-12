@extends('layouts.app')

@section('page-content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 text-center">Employee Performance Evaluation</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-4">
                            <small>
                                Please ensure all evaluation criteria are filled out honestly and accurately. Incomplete or
                                biased evaluations may affect employee development and organizational goals. For detailed
                                instructions, see the <a href="/evaluation-guide.pdf" target="_blank">Evaluation Guide</a>.
                            </small>
                        </div>
                        <form>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="staff_name" class="form-label">Staff Name</label>
                                    <input type="text" class="form-control" id="staff_name" name="staff_name"
                                        placeholder="Enter staff name">
                                </div>
                                <div class="col-md-6">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department"
                                        placeholder="Enter department">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="employment_date" class="form-label">Employment Date</label>
                                    <input type="date" class="form-control" id="employment_date" name="employment_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="job_title" class="form-label">Job Title</label>
                                    <input type="text" class="form-control" id="job_title" name="job_title"
                                        placeholder="Enter job title">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="promotion" class="form-label">Promotion</label>
                                    <input type="text" class="form-control" id="promotion" name="promotion"
                                        placeholder="Promotion details">
                                </div>
                                <div class="col-md-6">
                                    <label for="transfer" class="form-label">Transfer</label>
                                    <input type="text" class="form-control" id="transfer" name="transfer"
                                        placeholder="Transfer details">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="evaluation_period_start" class="form-label">Evaluation Period Start</label>
                                    <input type="date" class="form-control" id="evaluation_period_start"
                                        name="evaluation_period_start">
                                </div>
                                <div class="col-md-6">
                                    <label for="evaluation_period_end" class="form-label">Evaluation Period End</label>
                                    <input type="date" class="form-control" id="evaluation_period_end"
                                        name="evaluation_period_end">
                                </div>
                            </div>
                            <hr>
                            <h5 class="mb-3">Evaluation Criteria (Rate 1-5)</h5>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="knowledge_of_job" class="form-label">Knowledge of Job</label>
                                    <input type="number" min="1" max="5" class="form-control" id="knowledge_of_job"
                                        name="knowledge_of_job">
                                </div>
                                <div class="col-md-6">
                                    <label for="quality_of_work" class="form-label">Quality of Work</label>
                                    <input type="number" min="1" max="5" class="form-control" id="quality_of_work"
                                        name="quality_of_work">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="quantity_of_work" class="form-label">Quantity of Work</label>
                                    <input type="number" min="1" max="5" class="form-control" id="quantity_of_work"
                                        name="quantity_of_work">
                                </div>
                                <div class="col-md-6">
                                    <label for="emotional_intelligence" class="form-label">Emotional Intelligence</label>
                                    <input type="number" min="1" max="5" class="form-control" id="emotional_intelligence"
                                        name="emotional_intelligence">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="time_management" class="form-label">Time Management</label>
                                    <input type="number" min="1" max="5" class="form-control" id="time_management"
                                        name="time_management">
                                </div>
                                <div class="col-md-6">
                                    <label for="initiative_and_creativity" class="form-label">Initiative and
                                        Creativity</label>
                                    <input type="number" min="1" max="5" class="form-control" id="initiative_and_creativity"
                                        name="initiative_and_creativity">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="team_work" class="form-label">Team Work</label>
                                    <input type="number" min="1" max="5" class="form-control" id="team_work"
                                        name="team_work">
                                </div>
                                <div class="col-md-6">
                                    <label for="accountablity" class="form-label">Accountability</label>
                                    <input type="number" min="1" max="5" class="form-control" id="accountablity"
                                        name="accountablity">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="attendance_and_punctuality" class="form-label">Attendance and
                                        Punctuality</label>
                                    <input type="number" min="1" max="5" class="form-control"
                                        id="attendance_and_punctuality" name="attendance_and_punctuality">
                                </div>
                                <div class="col-md-6">
                                    <label for="company_resource_usage_and_protection" class="form-label">Company Resource
                                        Usage & Protection</label>
                                    <input type="number" min="1" max="5" class="form-control"
                                        id="company_resource_usage_and_protection"
                                        name="company_resource_usage_and_protection">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label for="communication_skills" class="form-label">Communication Skills</label>
                                    <input type="number" min="1" max="5" class="form-control" id="communication_skills"
                                        name="communication_skills">
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
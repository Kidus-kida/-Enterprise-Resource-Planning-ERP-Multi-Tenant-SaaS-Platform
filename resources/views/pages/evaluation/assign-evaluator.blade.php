@extends('layouts.app')
@section('page-content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                @if(request()->is('evaluate'))
                    {{-- Only show the evaluation results table on /evaluate --}}
                    @if(isset($evaluations) && $evaluations->whereNotNull('percentage_score')->count())
                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Evaluation Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="evaluation-table" class="table table-striped custom-table w-100">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Evaluator</th>
                                                <th>Period</th>
                                                <th>Score (%)</th>
                                                <th>Average</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($evaluations->whereNotNull('percentage_score') as $i => $eval)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $eval->staff_name ?? '-' }}</td>
                                                    <td>{{ $eval->department ?? '-' }}</td>
                                                    <td>{{ $eval->job_title ?? '-' }}</td>
                                                    <td>{{ $eval->evaluator_name ?? '-' }}</td>
                                                    <td>{{ $eval->evaluation_period_start?->format('Y-m-d') }} -
                                                        {{ $eval->evaluation_period_end?->format('Y-m-d') }}
                                                    </td>
                                                    <td>{{ $eval->percentage_score ?? '-' }}%</td>
                                                    <td>{{ number_format($eval->average_score, 2) }}</td>
                                                    <td>{{ $eval->created_at?->format('Y-m-d') }}</td>
                                                    <td>
                                                        <a href="{{ url('/evaluation/start/' . $eval->employee_id) }}"
                                                            class="btn btn-sm btn-success" title="Start Evaluation">Start Evaluation</a>
                                                        <button type="button" class="btn btn-sm btn-danger open-delete-modal"
                                                            data-evaluation-id="{{ $eval->id }}">Delete</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @push('page-scripts')
                            @vite(["resources/js/datatables.js"])
                            <script type="module">
                                $(function () {
                                    $('#evaluation-table').DataTable();
                                });
                            </script>
                        @endpush
                    @endif
                @else
                    {{-- Show the assign form and employee list on other routes --}}
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0 text-center">Assign Evaluator to Employees</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search employees..."
                                        value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </div>
                            </form>
                            @php
                                $search = request('search');
                                $filteredEmployees = $employees;
                                if ($search) {
                                    $filteredEmployees = $employees->filter(function ($employee) use ($search) {
                                        $search = strtolower($search);
                                        return (
                                            str_contains(strtolower($employee->full_name ?? $employee->name), $search) ||
                                            str_contains(strtolower($employee->email), $search) ||
                                            str_contains(strtolower($employee->phone), $search) ||
                                            str_contains(strtolower(optional($employee->employeeDetail->department)->name), $search) ||
                                            str_contains(strtolower(optional($employee->employeeDetail->designation)->name), $search)
                                        );
                                    });
                                }
                            @endphp
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach($filteredEmployees as $employee)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $employee->full_name ?? $employee->name }}</td>
                                            <td>{{ $employee->email }}</td>
                                            <td>{{ $employee->phone }}</td>
                                            <td>{{ $employee->employeeDetail->department->name ?? '-' }}</td>
                                            <td>{{ $employee->employeeDetail->designation->name ?? '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary open-assign-modal"
                                                    data-employee-id="{{ $employee->id }}">Assign</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if(isset($evaluations) && $evaluations->whereNotNull('percentage_score')->count())
                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Evaluation Results</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="evaluation-table" class="table table-striped custom-table w-100">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Evaluator</th>
                                                <th>Period</th>
                                                <th>Score (%)</th>
                                                <th>Average</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($evaluations->whereNotNull('percentage_score') as $i => $eval)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $eval->staff_name ?? '-' }}</td>
                                                    <td>{{ $eval->department ?? '-' }}</td>
                                                    <td>{{ $eval->job_title ?? '-' }}</td>
                                                    <td>{{ $eval->evaluator_name ?? '-' }}</td>
                                                    <td>{{ $eval->evaluation_period_start?->format('Y-m-d') }} -
                                                        {{ $eval->evaluation_period_end?->format('Y-m-d') }}
                                                    </td>
                                                    <td>{{ $eval->percentage_score ?? '-' }}%</td>
                                                    <td>{{ number_format($eval->average_score, 2) }}</td>
                                                    <td>{{ $eval->created_at?->format('Y-m-d') }}</td>
                                                    <td>
                                                        {{-- Replace with a working route or a placeholder if not defined --}}
                                                        <a href="#" class="btn btn-sm btn-primary"
                                                            title="View details (route not defined)">View</a>
                                                        <button type="button" class="btn btn-sm btn-danger open-delete-modal"
                                                            data-evaluation-id="{{ $eval->id }}">Delete</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @push('page-scripts')
                            @vite(["resources/js/datatables.js"])
                            <script type="module">
                                $(function () {
                                    $('#evaluation-table').DataTable();
                                });
                            </script>
                        @endpush
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if(!request()->is('evaluate'))
        @php $allEmployees = $allEmployees ?? $employees; @endphp
        <!-- Assign Evaluator Modal -->
        <div class="modal fade" id="assignEvaluatorModal" tabindex="-1" aria-labelledby="assignEvaluatorModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="assignEvaluatorForm" method="POST" action="">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignEvaluatorModalLabel">Assign Evaluators</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="employee_id" id="modalEmployeeId">
                            <div class="mb-3">
                                <label>Select Evaluators:</label>
                                <input type="text" class="form-control mb-2" id="modalEvaluatorSearch"
                                    placeholder="Search evaluators...">
                                <div class="row flex-column" id="evaluatorList">
                                    @foreach($allEmployees as $evaluator)
                                        <div class="col-md-4 mb-2 evaluator-item">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="evaluators[]"
                                                    value="{{ $evaluator->id }}" id="evaluator_{{ $evaluator->id }}">
                                                <label class="form-check-label" for="evaluator_{{ $evaluator->id }}">
                                                    {{ $evaluator->full_name ?? $evaluator->name }} ({{ $evaluator->email }})
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Assign Selected</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEvaluationModal" tabindex="-1" aria-labelledby="deleteEvaluationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteEvaluationForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteEvaluationModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this evaluation?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.open-assign-modal').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var employeeId = this.getAttribute('data-employee-id');
                    document.getElementById('modalEmployeeId').value = employeeId;
                    // Uncheck all checkboxes and disable the one for the current employee
                    document.querySelectorAll('#assignEvaluatorModal input[type=checkbox]').forEach(function (cb) {
                        cb.checked = false;
                        cb.disabled = (cb.value == employeeId);
                    });
                    // Set form action (optional: update to your route if needed)
                    document.getElementById('assignEvaluatorForm').action = '/evaluation/assign';
                    var modal = new bootstrap.Modal(document.getElementById('assignEvaluatorModal'));
                    modal.show();
                });
            });

            // Modal search functionality
            document.getElementById('modalEvaluatorSearch').addEventListener('input', function () {
                var search = this.value.toLowerCase();
                document.querySelectorAll('#evaluatorList .evaluator-item').forEach(function (item) {
                    var label = item.textContent.toLowerCase();
                    item.style.display = label.includes(search) ? '' : 'none';
                });
            });

            document.querySelectorAll('.open-delete-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var evaluationId = this.getAttribute('data-evaluation-id');
                    var form = document.getElementById('deleteEvaluationForm');
                    form.action = '/evaluation/' + evaluationId;
                    var modal = new bootstrap.Modal(document.getElementById('deleteEvaluationModal'));
                    modal.show();
                });
            });
        });
    </script>
@endsection

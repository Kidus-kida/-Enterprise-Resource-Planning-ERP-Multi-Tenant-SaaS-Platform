@extends('layouts.app')
@section('page-content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
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
                                @foreach($employees as $employee)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $employee->full_name ?? $employee->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $employee->phone }}</td>
                                        <td>{{ $employee->employeeDetail->department->name ?? '-' }}</td>
                                        <td>{{ $employee->employeeDetail->designation->name ?? '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success open-assign-modal"
                                                data-employee-id="{{ $employee->id }}">Assign</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            <input type="text" class="form-control mb-2" id="modalEvaluatorSearch" placeholder="Search evaluators...">
                            <div class="row" id="evaluatorList">
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
            document.getElementById('modalEvaluatorSearch').addEventListener('input', function() {
                var search = this.value.toLowerCase();
                document.querySelectorAll('#evaluatorList .evaluator-item').forEach(function(item) {
                    var label = item.textContent.toLowerCase();
                    item.style.display = label.includes(search) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
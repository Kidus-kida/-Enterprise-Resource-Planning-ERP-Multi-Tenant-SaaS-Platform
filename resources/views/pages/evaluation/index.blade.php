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
                        @if(isset($assignedEmployees) && $assignedEmployees->count())
                            <div class="mb-4">
                                <h5>Employees Assigned to You for Evaluation</h5>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignedEmployees as $i => $employee)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $employee->full_name ?? $employee->name }}</td>
                                                <td>{{ $employee->email }}</td>
                                                <td>{{ $employee->employeeDetail->department->name ?? '-' }}</td>
                                                <td>{{ $employee->employeeDetail->designation->name ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('evaluation.form', $employee->id) }}"
                                                        class="btn btn-sm btn-primary">Start Evaluation</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No employees assigned to you for evaluation.</div>
                        @endif
                        <div class="alert alert-warning mb-4">
                            <small>
                                Please ensure all evaluation criteria are filled out honestly and accurately. Incomplete or
                                biased evaluations may affect employee development and organizational goals. For detailed
                                instructions, see the <a href="/evaluation-guide.pdf" target="_blank">Evaluation Guide</a>.
                            </small>
                        </div>
                        {{-- The evaluation form is now only shown on the evaluation.form route --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
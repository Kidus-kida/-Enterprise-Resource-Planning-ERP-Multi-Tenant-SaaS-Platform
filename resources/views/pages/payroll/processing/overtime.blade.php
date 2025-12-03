@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Enter Overtime Details') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('payroll.processing.index') }}">{{ __('Payroll Processing') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Overtime') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('payroll.processing.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="period_start" value="{{ $periodStart }}">
                            <input type="hidden" name="period_end" value="{{ $periodEnd }}">
                            <input type="hidden" name="pay_date" value="{{ $payDate }}">

                            <div class="alert alert-info mb-4">
                                <strong>{{ __('Period:') }}</strong> {{ format_date($periodStart) }} - {{ format_date($periodEnd) }}<br>
                                <strong>{{ __('Selected Employees:') }}</strong> {{ $employees->count() }}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Employee') }}</th>
                                            <th>{{ __('Basic Salary') }}</th>
                                            <th>{{ __('Working Days') }} <i class="fa fa-info-circle text-muted" data-bs-toggle="tooltip" title="Calculated from period days minus holidays and approved leaves. Sundays are counted as worked days."></i></th>
                                            <th>{{ __('Regular OT (Hours)') }} <small class="text-muted">x{{ \App\Models\PayrollSetting::get('overtime_regular_rate', 1.5) }}</small></th>
                                            <th>{{ __('Sunday OT (Hours)') }} <small class="text-muted">x{{ \App\Models\PayrollSetting::get('overtime_sunday_rate', 2.0) }}</small></th>
                                            <th>{{ __('Holiday OT (Hours)') }} <small class="text-muted">x{{ \App\Models\PayrollSetting::get('overtime_holiday_rate', 2.5) }}</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $employee)
                                            <tr>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="#" class="avatar"><img alt="" src="{{ !empty($employee->avatar) ? uploadedAsset($employee->avatar,'users') : asset('images/user.jpg') }}"></a>
                                                        <a href="#">{{ $employee->fullname }} <span>{{ $employee->designation }}</span></a>
                                                    </h2>
                                                    <input type="hidden" name="employees[]" value="{{ $employee->employeeDetail->id }}">
                                                </td>
                                                <td>
                                                    {{ LocaleSettings('currency_symbol') }} {{ number_format($employee->employeeDetail->salaryDetails->base_salary ?? 0, 2) }}
                                                </td>
                                                <td>
                                                    <input type="number" step="0.5" min="0" name="working_days[{{ $employee->employeeDetail->id }}]" class="form-control" value="{{ $workingDaysData[$employee->employeeDetail->id] ?? 0 }}" style="width: 80px;">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.5" min="0" name="overtime_regular[{{ $employee->employeeDetail->id }}]" class="form-control" value="0" style="width: 100px;">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.5" min="0" name="overtime_sunday[{{ $employee->employeeDetail->id }}]" class="form-control" value="0" style="width: 100px;">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.5" min="0" name="overtime_holiday[{{ $employee->employeeDetail->id }}]" class="form-control" value="0" style="width: 100px;">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="submit-section mt-4">
                                <button type="submit" class="btn btn-primary submit-btn">{{ __('Calculate & Generate Payroll') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

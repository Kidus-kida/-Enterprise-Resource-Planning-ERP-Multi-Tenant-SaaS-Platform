@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Create Payroll') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('payroll.processing.index') }}">{{ __('Payroll Processing') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('payroll.processing.select-employees') }}" method="POST">
                            @csrf
                            
                            <!-- Period Selection -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Period Start') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="period_start" class="form-control" required value="{{ date('Y-m-01') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Period End') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="period_end" class="form-control" required value="{{ date('Y-m-t') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Pay Date') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="pay_date" class="form-control" required value="{{ date('Y-m-t') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Selection Method -->
                            <div class="form-group mb-4">
                                <label class="d-block mb-2">{{ __('Selection Method') }}</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="selection_method" id="method_individual" value="individual" checked>
                                    <label class="form-check-label" for="method_individual">{{ __('Select Employees') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="selection_method" id="method_department" value="department">
                                    <label class="form-check-label" for="method_department">{{ __('Select by Department') }}</label>
                                </div>
                            </div>

                            <!-- Individual Selection -->
                            <div id="individual_selection" class="mb-4">
                                <div class="form-group">
                                    <label>{{ __('Employees') }}</label>
                                    <select name="employees[]" class="select form-control" multiple>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->fullname }} ({{ $employee->employeeDetail->employee_id ?? '-' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Department Selection -->
                            <div id="department_selection" class="mb-4" style="display: none;">
                                <div class="form-group">
                                    <label>{{ __('Department') }}</label>
                                    <select name="department_id" class="select form-control">
                                        <option value="">{{ __('Select Department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">{{ __('Next: Enter Overtime') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script>

function initPayrollProcessing() {
    // Ensure jQuery is available before running
    if (typeof $ === 'undefined') {
        setTimeout(initPayrollProcessing, 50);
        return;
    }
    
    $('input[name="selection_method"]').off('change').on('change', function() {
        if ($(this).val() === 'individual') {
            $('#individual_selection').show();
            $('#department_selection').hide();
        } else {
            $('#individual_selection').hide();
            $('#department_selection').show();
        }
    });
}

// Run on initial load and Livewire navigation
document.addEventListener('livewire:navigated', initPayrollProcessing);
window.addEventListener('load', initPayrollProcessing);
</script>
@endpush

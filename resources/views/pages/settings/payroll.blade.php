@extends('pages.settings.index')

@section('page-header-section')
    <!-- Page Header -->
    <x-breadcrumb>
        <x-slot name="title">{{ __('Payroll Settings') }}</x-slot>
    </x-breadcrumb>
    <!-- /Page Header -->
@endsection

@section('page-section')
    
    <!-- Tax Brackets Section -->
    <form action="{{ route('settings.payroll.tax-brackets.update') }}" method="post" id="tax-brackets-form">
        @csrf
        <div class="card settings-widget mb-4">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-1">
                            <i class="la la-percentage text-primary"></i> {{ __('Tax Brackets') }}
                        </h4>
                        <p class="text-muted mb-0 small">{{ __('Define progressive tax rates based on salary ranges') }}</p>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-primary" id="add-tax-bracket">
                            <i class="fa fa-plus"></i> {{ __('Add Bracket') }}
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0" id="tax-brackets-table">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Min Amount') }}</th>
                                <th>{{ __('Max Amount') }}</th>
                                <th>{{ __('Tax Rate (%)') }}</th>
                                <th style="width: 80px;" class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxBrackets as $bracket)
                                <tr>
                                    <td>
                                        <input type="number" name="brackets[{{ $loop->index }}][min_amount]" class="form-control form-control-sm" 
                                            value="{{ $bracket->min_amount }}" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" name="brackets[{{ $loop->index }}][max_amount]" class="form-control form-control-sm" 
                                            value="{{ $bracket->max_amount }}" step="0.01" placeholder="{{ __('No limit') }}">
                                    </td>
                                    <td>
                                        <input type="number" name="brackets[{{ $loop->index }}][tax_rate]" class="form-control form-control-sm" 
                                            value="{{ $bracket->tax_rate }}" step="0.01" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger delete-bracket">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="no-brackets">
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="la la-info-circle"></i> {{ __('No tax brackets defined. Click "Add Bracket" to create one.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> {{ __('Save Tax Brackets') }}
                </button>
            </div>
        </div>
    </form>
    <!-- /Tax Brackets Section -->

    <!-- Payroll Settings Form -->
    <form action="{{ route('settings.payroll.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        
        <!-- Pension Settings -->
        <div class="card settings-widget mb-4">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="la la-shield text-success"></i> {{ __('Pension Contribution') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-user text-primary"></i> {{ __('Employee Contribution (%)') }}
                            </label>
                            <input type="number" class="form-control" name="pension_employee_percent" 
                                value="{{ $settings['pension_employee_percent'] ?? 7 }}" step="0.01" required>
                            <small class="form-text text-muted">{{ __('Deducted from employee salary') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-building text-info"></i> {{ __('Employer Contribution (%)') }}
                            </label>
                            <input type="number" class="form-control" name="pension_employer_percent" 
                                value="{{ $settings['pension_employer_percent'] ?? 11 }}" step="0.01" required>
                            <small class="form-text text-muted">{{ __('Company contribution amount') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Pension Settings -->

        <!-- Overtime Rates -->
        <div class="card settings-widget mb-4">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="la la-clock text-warning"></i> {{ __('Overtime Rates (Multipliers)') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-calendar text-primary"></i> {{ __('Regular Overtime') }}
                            </label>
                            <input type="number" class="form-control" name="overtime_regular_rate" 
                                value="{{ $settings['overtime_regular_rate'] ?? 1.5 }}" step="0.1" required>
                            <small class="form-text text-muted">{{ __('Default: 1.5x base rate') }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-sun text-warning"></i> {{ __('Sunday Overtime') }}
                            </label>
                            <input type="number" class="form-control" name="overtime_sunday_rate" 
                                value="{{ $settings['overtime_sunday_rate'] ?? 2.0 }}" step="0.1" required>
                            <small class="form-text text-muted">{{ __('Default: 2.0x base rate') }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-gift text-danger"></i> {{ __('Holiday Overtime') }}
                            </label>
                            <input type="number" class="form-control" name="overtime_holiday_rate" 
                                value="{{ $settings['overtime_holiday_rate'] ?? 2.5 }}" step="0.1" required>
                            <small class="form-text text-muted">{{ __('Default: 2.5x base rate') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Overtime Rates -->

        <!-- Taxable Allowance Thresholds -->
        <div class="card settings-widget mb-4">
            <div class="card-header bg-light">
                <h4 class="card-title mb-1">
                    <i class="la la-money text-success"></i> {{ __('Taxable Allowance Thresholds') }}
                </h4>
                <p class="text-muted mb-0 small">{{ __('Allowances exceeding these amounts will be considered taxable income') }}</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-users text-primary"></i> {{ __('Regular Employees') }}
                            </label>
                            <input type="number" class="form-control" name="taxable_allowance_regular" 
                                value="{{ $settings['taxable_allowance_regular'] ?? 600 }}" step="0.01" required>
                            <small class="form-text text-muted">{{ __('Non-managerial staff threshold') }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-user-tie text-info"></i> {{ __('Managerial Employees') }}
                            </label>
                            <input type="number" class="form-control" name="taxable_allowance_managerial" 
                                value="{{ $settings['taxable_allowance_managerial'] ?? 2200 }}" step="0.01" required>
                            <small class="form-text text-muted">{{ __('Managers and directors threshold') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Taxable Allowance Thresholds -->

        <!-- General Payroll Settings -->
        <div class="card settings-widget mb-4">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="la la-cog text-secondary"></i> {{ __('General Settings') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-calendar-check text-primary"></i> {{ __('Pay Period') }}
                            </label>
                            <select class="form-control form-select" name="pay_period">
                                <option value="monthly" {{ ($settings['pay_period'] ?? 'monthly') == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                <option value="biweekly" {{ ($settings['pay_period'] ?? '') == 'biweekly' ? 'selected' : '' }}>{{ __('Bi-weekly') }}</option>
                                <option value="weekly" {{ ($settings['pay_period'] ?? '') == 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-briefcase text-info"></i> {{ __('Working Days per Week') }}
                            </label>
                            <input type="number" class="form-control" name="working_days_per_week" 
                                value="{{ $settings['working_days_per_week'] ?? 5 }}" min="1" max="7" required>
                            <small class="form-text text-muted">{{ __('Used for overtime calculations') }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-block mb-0">
                            <label class="col-form-label fw-semibold">
                                <i class="la la-clock-o text-warning"></i> {{ __('Working Hours per Day') }}
                            </label>
                            <input type="number" class="form-control" name="working_hours_per_day" 
                                value="{{ $settings['working_hours_per_day'] ?? 8 }}" min="1" max="24" required>
                            <small class="form-text text-muted">{{ __('Standard working hours') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /General Payroll Settings -->

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fa fa-save"></i> {{ __('Save All Settings') }}
            </button>
        </div>
    </form>
    <!-- /Payroll Settings Form -->

@endsection

@push('page-scripts')
<script>
$(document).ready(function() {
    let bracketIndex = {{ count($taxBrackets) }};

    // Add new tax bracket
    $('#add-tax-bracket').on('click', function() {
        $('.no-brackets').remove();
        
        const newRow = `
            <tr>
                <td>
                    <input type="number" name="brackets[${bracketIndex}][min_amount]" class="form-control" 
                        step="0.01" required placeholder="0.00">
                </td>
                <td>
                    <input type="number" name="brackets[${bracketIndex}][max_amount]" class="form-control" 
                        step="0.01" placeholder="{{ __('No limit') }}">
                </td>
                <td>
                    <input type="number" name="brackets[${bracketIndex}][tax_rate]" class="form-control" 
                        step="0.01" required placeholder="0.00">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger delete-bracket">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#tax-brackets-table tbody').append(newRow);
        bracketIndex++;
    });

    // Delete tax bracket
    $(document).on('click', '.delete-bracket', function() {
        $(this).closest('tr').remove();
        
        // Re-index remaining brackets
        $('#tax-brackets-table tbody tr').each(function(index) {
            $(this).find('input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/brackets\[\d+\]/, `brackets[${index}]`);
                    $(this).attr('name', newName);
                }
            });
        });
        
        bracketIndex = $('#tax-brackets-table tbody tr').length;
        
        if (bracketIndex === 0) {
            $('#tax-brackets-table tbody').html('<tr class="no-brackets"><td colspan="4" class="text-center text-muted">{{ __('No tax brackets defined. Click "Add Bracket" to create one.') }}</td></tr>');
        }
    });
});
</script>
@endpush

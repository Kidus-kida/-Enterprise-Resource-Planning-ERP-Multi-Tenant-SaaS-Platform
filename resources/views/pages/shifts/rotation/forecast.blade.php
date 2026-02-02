@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('shifts.rotation.index') }}" class="btn btn-outline-secondary">
                        <i class="la la-arrow-left"></i> {{ __('Back to Rotations') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row align-items-end mb-4">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-bold">{{ __('Select Employee') }}</label>
                                    <select id="employee_id" class="form-select select2">
                                        <option value="">{{ __('Select an employee...') }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{ $employee->full_name }} ({{ $employee->employeeDetail->department->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-bold">{{ __('Select Anchor Shift') }}</label>
                                    <select id="shift_id" class="form-select select2">
                                        <option value="">{{ __('Select a shift...') }}</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">
                                                {{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-bold">{{ __('Start Date') }}</label>
                                    <input type="date" id="start_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-bold">{{ __('End Date') }}</label>
                                    <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="generateBtn" class="btn btn-primary w-100" style="margin-top: 1.5rem;">
                                    <i class="la la-sync-alt"></i> {{ __('Generate') }}
                                </button>
                            </div>
                        </div>

                        <div id="forecastResult" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped custom-table mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Day') }}</th>
                                            <th>{{ __('Shift Name') }}</th>
                                            <th>{{ __('Start Time') }}</th>
                                            <th>{{ __('End Time') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="forecastTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div id="emptyState" class="text-center py-5">
                            <i class="la la-calendar-alt la-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('Select an employee or a shift and click Generate.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script>
    // Wait for DOM and ensure jQuery is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Fallback if $ is not available yet
        if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        // Initialize Select2 with fallback
        function initSelect2() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    width: '100%',
                    allowClear: true
                });
            } else {
                // Fallback to regular select if Select2 is not available
                console.warn('Select2 not available, using regular selects');
            }
        }

        // Try to initialize Select2
        initSelect2();

        const generateBtn = document.getElementById('generateBtn');
        const employeeSelect = $('#employee_id');
        const shiftSelect = $('#shift_id');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const resultDiv = document.getElementById('forecastResult');
        const tableBody = document.getElementById('forecastTableBody');
        const emptyState = document.getElementById('emptyState');

        // Mutual exclusivity - only if Select2 is available
        if (typeof $.fn.select2 !== 'undefined') {
            employeeSelect.on('change', function() {
                if ($(this).val()) {
                    shiftSelect.val(null).trigger('change.select2');
                }
            });

            shiftSelect.on('change', function() {
                if ($(this).val()) {
                    employeeSelect.val(null).trigger('change.select2');
                }
            });
        } else {
            // Fallback for regular selects
            employeeSelect.on('change', function() {
                if ($(this).val()) {
                    shiftSelect.val('');
                }
            });

            shiftSelect.on('change', function() {
                if ($(this).val()) {
                    employeeSelect.val('');
                }
            });
        }

        // Button click handler
        if (generateBtn) {
            generateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const userId = employeeSelect.val();
                const shiftId = shiftSelect.val();
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                if (!userId && !shiftId) {
                    alert('{{ __("Please select either an employee or a shift.") }}');
                    return;
                }

                generateBtn.disabled = true;
                generateBtn.innerHTML = '<i class="la la-spinner la-spin"></i> {{ __("Processing...") }}';

                let url = `{{ route('shifts.rotation.forecast.data') }}?start_date=${startDate}&end_date=${endDate}`;
                if (userId) url += `&user_id=${userId}`;
                else url += `&shift_id=${shiftId}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            tableBody.innerHTML = '';
                            if (data.data.length === 0) {
                                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">{{ __("No data found for this period.") }}</td></tr>';
                            }
                            data.data.forEach(item => {
                                const row = `
                                    <tr class="${item.is_off ? 'table-light' : ''}">
                                        <td>${item.date}</td>
                                        <td>${item.day}</td>
                                        <td><strong>${item.shift_name}</strong></td>
                                        <td>${item.start_time}</td>
                                        <td>${item.end_time}</td>
                                        <td>
                                            <span class="badge ${item.is_off ? 'bg-secondary' : 'bg-success'}">
                                                ${item.is_off ? '{{ __("OFF") }}' : '{{ __("WORK") }}'}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                                tableBody.insertAdjacentHTML('beforeend', row);
                            });
                            resultDiv.classList.remove('d-none');
                            emptyState.classList.add('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __("An error occurred while generating the forecast.") }}');
                    })
                    .finally(() => {
                        generateBtn.disabled = false;
                        generateBtn.innerHTML = '<i class="la la-sync-alt"></i> {{ __("Generate") }}';
                    });
            });
        } else {
            console.error('Generate button not found!');
        }
    });

    // Also listen for Livewire navigation
    document.addEventListener('livewire:navigated', function() {
        // Re-initialize if navigated via Livewire
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                width: '100%',
                allowClear: true
            });
        }
    });
</script>
@endpush

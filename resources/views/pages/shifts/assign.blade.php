@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
                        <li class="breadcrumb-item active">Assign Employees</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-primary">
                        <i class="la la-list"></i> {{ __('View All Shifts') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('shifts.assign.store') }}" method="POST" id="assign-form">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="shift_id" class="form-label">{{ __('Select Shift') }} <span class="text-danger">*</span></label>
                                    <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror" required>
                                        <option value="">{{ __('-- Select Shift --') }}</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ (old('shift_id') == $shift->id || request('shift_id') == $shift->id) ? 'selected' : '' }}>
                                                {{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shift_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="effective_from" class="form-label">{{ __('Effective From') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="effective_from" id="effective_from" 
                                           class="form-control @error('effective_from') is-invalid @enderror" 
                                           value="{{ old('effective_from', date('Y-m-d')) }}" required>
                                    @error('effective_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="effective_until" class="form-label">{{ __('Effective Until') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input type="date" name="effective_until" id="effective_until" 
                                           class="form-control @error('effective_until') is-invalid @enderror" 
                                           value="{{ old('effective_until') }}">
                                    @error('effective_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">{{ __('Available Employees') }}</h5>
                                            <small class="text-muted">{{ __('Select employees to assign to this shift') }}</small>
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            @if($employees->isEmpty())
                                                <p class="text-muted text-center py-3">{{ __('No employees available') }}</p>
                                            @else
                                                <!-- Step 1: Filters -->
                                                <div class="row mb-3 g-2 p-2 bg-light rounded mx-0">
                                                    <div class="col-md-4">
                                                        <label class="small fw-bold mb-1 d-block text-muted text-uppercase">{{ __('By Department') }}</label>
                                                        <select id="filter_dept" class="form-select form-select-sm border-primary-subtle">
                                                            <option value="">{{ __('All Departments') }}</option>
                                                            @foreach($departments as $dept)
                                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="small fw-bold mb-1 d-block text-muted text-uppercase">{{ __('By Role') }}</label>
                                                        <select id="filter_desig" class="form-select form-select-sm border-primary-subtle">
                                                            <option value="">{{ __('All Roles') }}</option>
                                                            @foreach($designations as $desig)
                                                                <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="small fw-bold mb-1 d-block text-muted text-uppercase">{{ __('Search Employee') }}</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" id="employee_search" class="form-control border-primary-subtle" placeholder="{{ __('Name starts with...') }}">
                                                            <button class="btn btn-outline-primary" type="button"><i class="la la-search"></i></button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Step 2: Group Selection Actions (Now below filters) -->
                                                <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input border-primary" type="checkbox" id="select_all">
                                                        <label class="form-check-label fw-bold" for="select_all">
                                                            {{ __('Select All Visible') }}
                                                        </label>
                                                    </div>
                                                    <div id="filter_counter" class="small text-muted fw-bold">
                                                        {{ count($employees) }} {{ __('Employees Found') }}
                                                    </div>
                                                </div>

                                                <hr class="mt-0">
                                                
                                                <!-- Step 3: Resulting List -->
                                                <div id="employee_list_container">
                                                    @foreach($employees as $employee)
                                                        <div class="form-check mb-2 employee-row" 
                                                             data-dept="{{ $employee->employeeDetail?->department_id }}" 
                                                             data-desig="{{ $employee->employeeDetail?->designation_id }}"
                                                             data-firstname="{{ strtolower($employee->firstname) }}"
                                                             data-lastname="{{ strtolower($employee->lastname) }}">
                                                            <input class="form-check-input employee-checkbox" type="checkbox" 
                                                                   name="user_ids[]" value="{{ $employee->id }}" 
                                                                   id="emp_{{ $employee->id }}">
                                                            <label class="form-check-label" for="emp_{{ $employee->id }}">
                                                                <strong>{{ $employee->firstname }} {{ $employee->lastname }}</strong>
                                                                @if($employee->employeeDetail?->department || $employee->employeeDetail?->designation)
                                                                    <br><small class="text-info">
                                                                        {{ $employee->employeeDetail?->department?->name }} 
                                                                        {{ $employee->employeeDetail?->designation ? ' - ' . $employee->employeeDetail->designation->name : '' }}
                                                                    </small>
                                                                @endif
                                                                @if($employee->email)
                                                                    <br><small class="text-muted">{{ $employee->email }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">{{ __('Currently Assigned Employees') }}</h5>
                                            <small class="text-muted">{{ __('Employees already assigned to shifts') }}</small>
                                        </div>
                                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                            @php
                                                $hasAssignments = false;
                                            @endphp
                                            @foreach($shifts as $shift)
                                                @if(isset($assignments[$shift->id]) && $assignments[$shift->id]->isNotEmpty())
                                                    @php $hasAssignments = true; @endphp
                                                    <div class="shift-assignment-group" id="shift-group-{{ $shift->id }}">
                                                        <h6 class="text-primary d-flex justify-content-between align-items-center">
                                                            {{ $shift->name }}
                                                            @if(request('shift_id') == $shift->id)
                                                                <span class="badge badge-soft-primary small" style="font-size: 10px;">{{ __('SELECTED') }}</span>
                                                            @endif
                                                        </h6>
                                                    @foreach($assignments[$shift->id] as $assignment)
                                                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                                            <div>
                                                                <strong>{{ $assignment->user->firstname }} {{ $assignment->user->lastname }}</strong>
                                                                <br><small class="text-muted">
                                                                    From: {{ date('M d, Y', strtotime($assignment->effective_from)) }}
                                                                    @if($assignment->effective_until)
                                                                        - Until: {{ date('M d, Y', strtotime($assignment->effective_until)) }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="removeAssignment({{ $assignment->id }})" 
                                                                    title="{{ __('Remove') }}">
                                                                <i class="la la-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                        <hr>
                                                    </div>
                                                @endif
                                            @endforeach
                                            
                                            @if(!$hasAssignments)
                                                <p class="text-muted text-center py-3">{{ __('No employees assigned yet') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-5 pb-5">
                                <div class="col-12 text-end" style="position: relative; z-index: 9999;">
                                    <hr>
                                    <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary me-2 btn-lg">
                                        <i class="la la-times"></i> {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg px-5" style="cursor: pointer; position: relative; z-index: 10000;">
                                        <i class="la la-check"></i> {{ __('Assign Employees') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Hidden form for removal --}}
                        <form id="global-remove-form" action="{{ route('shifts.assign.remove') }}" method="POST" style="display:none;">
                            @csrf
                            <input type="hidden" name="assignment_id" id="assignment_to_remove">
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shift Assignment initialized');
    
    const assignForm = document.getElementById('assign-form');
    if (assignForm) {
        assignForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="la la-spinner la-spin"></i> {{ __("Processing...") }}';
            }
        });
    }

    const selectAll = document.getElementById('select_all');
    const deptFilter = document.getElementById('filter_dept');
    const desigFilter = document.getElementById('filter_desig');
    const searchFilter = document.getElementById('employee_search');
    const employeeRows = document.querySelectorAll('.employee-row');
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    const filterCounter = document.getElementById('filter_counter');

    // Filtering Logic
    function filterEmployees() {
        const deptId = deptFilter?.value.trim() || '';
        const desigId = desigFilter?.value.trim() || '';
        const searchQuery = searchFilter?.value.toLowerCase().trim() || '';
        let count = 0;

        employeeRows.forEach(row => {
            const rowDeptId = (row.dataset.dept || '').trim();
            const rowDesigId = (row.dataset.desig || '').trim();
            const firstname = (row.dataset.firstname || '').trim();
            const lastname = (row.dataset.lastname || '').trim();
            
            const matchesDept = !deptId || rowDeptId === deptId;
            const matchesDesig = !desigId || rowDesigId === desigId;
            
            // Name Prefix Matching (Starts With)
            // Checks if either firstname or lastname starts with the query
            const matchesSearch = !searchQuery || 
                                 firstname.startsWith(searchQuery) || 
                                 lastname.startsWith(searchQuery) ||
                                 `${firstname} ${lastname}`.startsWith(searchQuery);

            if (matchesDept && matchesDesig && matchesSearch) {
                row.classList.remove('d-none');
                count++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (filterCounter) {
            filterCounter.textContent = `${count} ${count === 1 ? '{{ __("Employee") }}' : '{{ __("Employees") }}'} {{ __("Found") }}`;
        }

        updateSelectAllState();
    }

    deptFilter?.addEventListener('change', filterEmployees);
    desigFilter?.addEventListener('change', filterEmployees);
    searchFilter?.addEventListener('input', filterEmployees);

    // Select All checkbox - Only affects VISIBLE items
    selectAll?.addEventListener('change', function() {
        const visibleCheckboxes = document.querySelectorAll('.employee-row:not(.d-none) .employee-checkbox');
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update Select All state based on visible items
    function updateSelectAllState() {
        if (!selectAll) return;
        const visibleCheckboxes = document.querySelectorAll('.employee-row:not(.d-none) .employee-checkbox');
        const checkedVisible = document.querySelectorAll('.employee-row:not(.d-none) .employee-checkbox:checked');
        
        if (visibleCheckboxes.length > 0) {
            selectAll.disabled = false;
            selectAll.checked = visibleCheckboxes.length === checkedVisible.length;
            selectAll.indeterminate = (checkedVisible.length > 0 && checkedVisible.length < visibleCheckboxes.length);
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            selectAll.disabled = true;
        }
    }

    // Removal Function
    window.removeAssignment = function(id) {
        if(confirm('{{ __("Remove employee from this shift?") }}')) {
            const removeForm = document.getElementById('global-remove-form');
            const removeInput = document.getElementById('assignment_to_remove');
            if (removeForm && removeInput) {
                removeInput.value = id;
                removeForm.submit();
            }
        }
    }

    // Individual checkbox listeners
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    // Initial run
    filterEmployees();

    // Handle shift filtering from URL
    const urlParams = new URLSearchParams(window.location.search);
    const targetShiftId = urlParams.get('shift_id');
    if (targetShiftId) {
        const assignmentGroups = document.querySelectorAll('.shift-assignment-group');
        assignmentGroups.forEach(group => {
            if (group.id !== `shift-group-${targetShiftId}`) {
                group.style.display = 'none';
            } else {
                group.style.backgroundColor = '#f8f9fa';
                group.style.padding = '10px';
                group.style.borderRadius = '8px';
                group.style.border = '1px solid #e3e3e3';
                
                // Scroll into view within the scrollable container
                setTimeout(() => {
                    group.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 500);
            }
        });

        // Add a "Show All" link to the header
        const assignedHeader = document.querySelector('.card-header.bg-light h5.mb-0');
        if (assignedHeader && !document.getElementById('show-all-assignments')) {
            const showAll = document.createElement('a');
            showAll.id = 'show-all-assignments';
            showAll.href = window.location.pathname;
            showAll.className = 'btn btn-xs btn-link float-end text-decoration-none';
            showAll.innerHTML = '<i class="la la-eye"></i> {{ __("Show All") }}';
            assignedHeader.parentElement.appendChild(showAll);
        }
    }
});
</script>
@endpush

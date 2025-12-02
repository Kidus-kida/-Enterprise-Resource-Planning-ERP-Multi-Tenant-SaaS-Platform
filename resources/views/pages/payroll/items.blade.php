@extends('layouts.app')


@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Payroll Items') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Payroll') }}
                </li>
            </ul>
           
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Page Tab -->
        <div class="page-menu">
            <div class="row">
                <div class="col-sm-12">
                    <ul class="nav nav-tabs nav-tabs-bottom">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab_additions">Additions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab_deductions">Deductions</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Tab -->
        
        <!-- Tab Content -->
        <div class="tab-content">
        
            <!-- Additions Tab -->
            <div class="tab-pane show active" id="tab_additions">
                <div class="payroll-table card">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="allowances-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">{{ __('Employee Name') }}</th>
                                    @foreach($allowanceTypes as $type)
                                        <th style="min-width: 150px;" class="allowance-column" data-column-name="{{ $type }}">
                                            {{ $type }}
                                            <button type="button" class="btn btn-sm btn-link text-danger delete-column-btn" data-type="allowance" data-name="{{ $type }}" title="{{ __('Delete Column') }}">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </th>
                                    @endforeach
                                    <th style="width: 50px;">
                                        <button type="button" class="btn btn-sm btn-primary" id="add-allowance-column" title="{{ __('Add Column') }}">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td><strong>{{ $employee->fullname }}</strong></td>
                                        @foreach($allowanceTypes as $type)
                                            @php
                                                $value = $allowances->where('employee_detail_id', $employee->employeeDetail->id)
                                                    ->where('name', $type)
                                                    ->first()->amount ?? '';
                                            @endphp
                                            <td>
                                                <input type="number" 
                                                    class="form-control editable-cell" 
                                                    data-employee-id="{{ $employee->employeeDetail->id }}" 
                                                    data-column-name="{{ $type }}"
                                                    data-type="allowance"
                                                    value="{{ $value }}" 
                                                    placeholder="-"
                                                    step="0.01"
                                                    min="0">
                                            </td>
                                        @endforeach
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($allowanceTypes) + 2 }}" class="text-center text-muted">
                                            {{ __('No employees found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Additions Tab -->

            
            <!-- Deductions Tab -->
            <div class="tab-pane" id="tab_deductions">
                <div class="payroll-table card">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="deductions-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">{{ __('Employee Name') }}</th>
                                    @foreach($deductionTypes as $type)
                                        <th style="min-width: 150px;" class="deduction-column" data-column-name="{{ $type }}">
                                            {{ $type }}
                                            <button type="button" class="btn btn-sm btn-link text-danger delete-column-btn" data-type="deduction" data-name="{{ $type }}" title="{{ __('Delete Column') }}">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </th>
                                    @endforeach
                                    <th style="width: 50px;">
                                        <button type="button" class="btn btn-sm btn-primary" id="add-deduction-column" title="{{ __('Add Column') }}">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td><strong>{{ $employee->fullname }}</strong></td>
                                        @foreach($deductionTypes as $type)
                                            @php
                                                $value = $deductions->where('employee_detail_id', $employee->employeeDetail->id)
                                                    ->where('name', $type)
                                                    ->first()->amount ?? '';
                                            @endphp
                                            <td>
                                                <input type="number" 
                                                    class="form-control editable-cell" 
                                                    data-employee-id="{{ $employee->employeeDetail->id }}" 
                                                    data-column-name="{{ $type }}"
                                                    data-type="deduction"
                                                    value="{{ $value }}" 
                                                    placeholder="-"
                                                    step="0.01"
                                                    min="0">
                                            </td>
                                        @endforeach
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($deductionTypes) + 2 }}" class="text-center text-muted">
                                            {{ __('No employees found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Deductions Tab -->
            
        </div>
        <!-- Tab Content -->
    </div>
@endsection


@push('page-scripts')
<script>
$(document).ready(function() {
    // Handle inline editing on blur
    $('.editable-cell').on('blur', function() {
        const $cell = $(this);
        const employeeId = $cell.data('employee-id');
        const columnName = $cell.data('column-name');
        const type = $cell.data('type');
        const value = $cell.val();

        // If empty, delete the record
        if (!value || value == '' || value == '0') {
            deleteItem(employeeId, columnName, type, $cell);
        } else {
            // Update or create
            saveItem(employeeId, columnName, type, value, $cell);
        }
    });

    // Save item function
    function saveItem(employeeId, columnName, type, value, $cell) {
        const url = type === 'allowance' 
            ? '{{ route("allowances.updateOrCreate") }}' 
            : '{{ route("deductions.updateOrCreate") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_id: employeeId,
                name: columnName,
                amount: value
            },
            beforeSend: function() {
                $cell.addClass('border-warning');
            },
            success: function(response) {
                $cell.removeClass('border-warning').addClass('border-success');
                setTimeout(() => $cell.removeClass('border-success'), 1000);
            },
            error: function(xhr) {
                $cell.removeClass('border-warning').addClass('border-danger');
                alert('Error saving data: ' + (xhr.responseJSON?.message || 'Unknown error'));
                setTimeout(() => $cell.removeClass('border-danger'), 2000);
            }
        });
    }

    // Delete item function
    function deleteItem(employeeId, columnName, type, $cell) {
        const url = type === 'allowance' 
            ? '{{ route("allowances.deleteByEmployee") }}' 
            : '{{ route("deductions.deleteByEmployee") }}';

        $.ajax({
            url: url,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                employee_id: employeeId,
                name: columnName
            },
            success: function(response) {
                $cell.val('');
            }
        });
    }

    // Add new allowance column
    $('#add-allowance-column').on('click', function() {
        const columnName = prompt('{{ __("Enter allowance name:") }}');
        if (columnName && columnName.trim()) {
            addColumn('allowance', columnName.trim());
        }
    });

    // Add new deduction column
    $('#add-deduction-column').on('click', function() {
        const columnName = prompt('{{ __("Enter deduction name:") }}');
        if (columnName && columnName.trim()) {
            addColumn('deduction', columnName.trim());
        }
    });

    // Add column function
    function addColumn(type, columnName) {
        const table = type === 'allowance' ? '#allowances-table' : '#deductions-table';
        const $headerRow = $(table + ' thead tr');
        const $addButton = $headerRow.find('th:last');
        
        // Add column header
        const newHeader = `<th style="min-width: 150px;" class="${type}-column" data-column-name="${columnName}">
            ${columnName}
            <button type="button" class="btn btn-sm btn-link text-danger delete-column-btn" data-type="${type}" data-name="${columnName}" title="{{ __('Delete Column') }}">
                <i class="fa fa-times"></i>
            </button>
        </th>`;
        $addButton.before(newHeader);

        // Add cells to each row
        $(table + ' tbody tr').each(function() {
            const $row = $(this);
            const $lastCell = $row.find('td:last');
            const employeeId = $row.find('.editable-cell').first().data('employee-id');
            
            if (employeeId) {
                const newCell = `<td>
                    <input type="number" 
                        class="form-control editable-cell" 
                        data-employee-id="${employeeId}" 
                        data-column-name="${columnName}"
                        data-type="${type}"
                        value="" 
                        placeholder="-"
                        step="0.01"
                        min="0">
                </td>`;
                $lastCell.before(newCell);
            }
        });

        // Re-bind blur event to new inputs
        $('.editable-cell').off('blur').on('blur', function() {
            const $cell = $(this);
            const employeeId = $cell.data('employee-id');
            const columnName = $cell.data('column-name');
            const type = $cell.data('type');
            const value = $cell.val();

            if (!value || value == '' || value == '0') {
                deleteItem(employeeId, columnName, type, $cell);
            } else {
                saveItem(employeeId, columnName, type, value, $cell);
            }
        });
    }

    // Delete column
    $(document).on('click', '.delete-column-btn', function() {
        if (!confirm('{{ __("Are you sure you want to delete this column? All data in this column will be lost.") }}')) {
            return;
        }

        const type = $(this).data('type');
        const columnName = $(this).data('name');
        const table = type === 'allowance' ? '#allowances-table' : '#deductions-table';
        
        // Find column index
        const $header = $(this).closest('th');
        const columnIndex = $header.index();
        
        // Remove header
        $header.remove();
        
        // Remove cells in all rows
        $(table + ' tbody tr').each(function() {
            $(this).find('td').eq(columnIndex).remove();
        });

        // Delete all items with this name from database
        const url = type === 'allowance' 
            ? '{{ route("allowances.index") }}' 
            : '{{ route("deductions.index") }}';
        
        // Reload page to ensure data consistency
        location.reload();
    });
});
</script>

<style>
.editable-cell {
    border: 1px solid #e0e0e0;
    text-align: right;
}

.editable-cell:focus {
    border-color: #1976d2;
    box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
}

.delete-column-btn {
    padding: 0 5px;
    font-size: 12px;
}

.delete-column-btn:hover {
    text-decoration: none;
}
</style>
@endpush


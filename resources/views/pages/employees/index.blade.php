@extends('layouts.app')

    <style>
        /* Viewport Layout fixes */
        .content {
            height: calc(100vh - 60px); 
            display: flex;
            flex-direction: column;
            overflow: hidden; 
            padding-bottom: 0 !important;
            padding-top: 0 !important;
        }

        .odoo-task-header {
            min-height: 50px;
            border-bottom: 1px solid #e3e6f0;
            background: #fff;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        
         .kanban-board {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: none;
            box-shadow: none;
            margin-bottom: 0 !important;
            background: transparent;
        }

        .kanban-board .card-body {
            flex: 1;
            display: flex;
            overflow: hidden;
            padding: 0;
        }
        
        .odoo-card {
            border-color: #e0e0e0;
            transition: all 0.2s;
            cursor: pointer;
        }
        .odoo-card:hover {
            border-color: #b0b0b0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
        }
        
        .odoo-employee-card {
            transition: all 0.2s;
            cursor: pointer;
        }
        .odoo-employee-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            border-color: #b0b0b0 !important;
        }
        .min-width-0 {
            min-width: 0;
        }
    </style>

@section('page-content')
    <div class="content container-fluid">

        <!-- Odoo-style Compact Header -->
        <div class="odoo-task-header d-flex align-items-center mb-2 px-1 gap-2">
            <!-- Left: Title & New Button -->
            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                <h4 class="mb-0 fw-bold">{{ __('Employees') }}</h4>
                <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add_employee">
                    <i class="fa fa-plus-circle me-1"></i> {{ __('New') }}
                </a>
            </div>

            <!-- Center: Search Bar -->
            <div class="d-flex justify-content-center flex-grow-1">
                 <div style="width: 100%; max-width: 500px;">
                    @php
                        $filterOptions = [
                            ['label' => 'My Department', 'value' => 'my_department'],
                            ['label' => 'Newly Hired', 'value' => 'newly_hired'],
                            ['label' => 'Archived', 'value' => 'archived'],
                        ];
                        $groupByOptions = [
                            ['label' => 'Department', 'value' => 'department'],
                            ['label' => 'Designation', 'value' => 'designation'],
                            ['label' => 'Location', 'value' => 'location'],
                        ];
                    @endphp
                     <x-odoo-search-bar 
                         action="{{ route('employees.index') }}" 
                         :fields="[
                            ['key' => 'name', 'label' => 'Name'],
                            ['key' => 'email', 'label' => 'Email'],
                            ['key' => 'phone', 'label' => 'Phone'],
                         ]"
                         :filterOptions="$filterOptions"
                         :groupByOptions="$groupByOptions"
                         targetSelector=".employee-grid-container"
                     />
                 </div>
            </div>

            <!-- Right: Action Menu -->
            <div class="flex-shrink-0">
                 <div class="view-icons d-flex align-items-center gap-1">
                    <a href="{{ route('employees.index') }}" class="grid-view btn btn-sm btn-light active"><i class="fa fa-th"></i></a>
                    <a href="{{ route('employees.list') }}" class="list-view btn btn-sm btn-light"><i class="fa-solid fa-bars"></i></a>
                </div>
            </div>
        </div>

        <div class="kanban-board card mb-0">
            <div class="card-body">
                <div class="employee-grid-container" style="flex: 1; overflow-y: auto; padding: 15px;">
                    @if(isset($isGrouped) && $isGrouped)
                        @foreach($employees as $groupKey => $groupItems)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5 class="fw-bold text-dark border-bottom pb-2">
                                        <i class="fa fa-layer-group me-2 text-secondary"></i> {{ $groupKey }}
                                        <span class="badge bg-light text-dark border ms-2 rounded-pill">{{ count($groupItems) }}</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="row px-1">
                                @foreach($groupItems as $employee)
                                     @include('pages.employees.partials.card', ['employee' => $employee])
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="row px-1">
                            @if (!empty($employees) && $employees->count() > 0)
                                @foreach ($employees as $employee)
                                    @include('pages.employees.partials.card', ['employee' => $employee])
                                @endforeach
                            @else
                                <div class="col-12 text-center py-5">
                                    <p class="text-muted">{{ __('No employees found') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    @include('pages.employees.modals.add_employee')
    @include('pages.employees.modals.create-job-position')
@endsection

@push('page-script')
<script>
    // Wait for Vite to load jQuery before executing
    window.addEventListener('load', function() {
        // Ensure jQuery is available
        if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded yet, retrying...');
            setTimeout(initEmployeeScripts, 100);
            return;
        }
        
        initEmployeeScripts();
    });

    // Handle Livewire navigation
    document.addEventListener('livewire:navigated', function() {
        initEmployeeScripts();
    });

    function initEmployeeScripts() {
        $(document).ready(function() {
            // Destroy existing instances if any to prevent conflicts with global app.js
            $('#add_employee .select').each(function() {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy');
                }
            });
            
             $('#add_job_position_modal .select').each(function() {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy');
                }
            });

            // Initialize Select2 specifically for these modals (double check to ensure they render correctly)
            $('#add_employee .select').select2({
                width: '100%',
                dropdownParent: $('#add_employee .modal-content') // Attach to modal content
            });

            $('#add_job_position_modal .select').select2({
                width: '100%',
                dropdownParent: $('#add_job_position_modal .modal-content')
            });

            // Job Position "Add New" Logic (moved from create.blade.php)
            $('#job_position').on('change', function() {
                if ($(this).val() === 'add_new') {
                    $(this).val('').trigger('change');
                    $('#add_job_position_modal').modal('show');
                }
            });

            $('#add_job_position_form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('.submit-btn');
                btn.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('job-positions.store') }}",
                    method: "POST",
                    data: form.serialize(),
                    success: function(response) {
                        btn.prop('disabled', false);
                        if(response.success) {
                            $('#add_job_position_modal').modal('hide');
                            // Add new option
                            var newOption = new Option(response.job_position.name, response.job_position.id, true, true);
                            // Append before the last option (Add New)
                            var addNewOption = $('#job_position option[value="add_new"]');
                            if(addNewOption.length > 0) {
                                addNewOption.before(newOption);
                            } else {
                                $('#job_position').append(newOption);
                            }
                            $('#job_position').val(response.job_position.id).trigger('change');
                            
                            // Reset form
                            form[0].reset();
                            form.find('select').val('').trigger('change');
                            
                            // Re-open first modal if it was closed or hidden (optional, but bootstrap usually handles stacking)
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    }
</script>
@endpush



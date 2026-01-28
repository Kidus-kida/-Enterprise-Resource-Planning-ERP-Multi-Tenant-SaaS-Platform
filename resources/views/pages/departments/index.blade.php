@extends('layouts.app')

@push('page-styles')
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

        /* Responsive spacing for filter form */
        .odoo-task-header .form-control {
            margin-bottom: 0;
            border-radius: 0;
            border-color: #ced4da;
        }
        
         .odoo-task-header .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .odoo-task-header .input-group-text {
            border-radius: 4px 0 0 4px;
            border-color: #ced4da;
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

        .department-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
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
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Odoo-style Compact Header -->
        <div class="odoo-task-header d-flex align-items-center mb-2 px-1 gap-2">
            <!-- Left: Title & New Button -->
            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                <h4 class="mb-0 fw-bold">{{ __('Departments') }}</h4>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm"
                   data-url="{{ route('departments.create') }}" data-ajax-modal="true"
                   data-size="md" data-title="{{ __('Add Department') }}">
                    {{ __('New') }}
                </a>
            </div>

            <!-- Center: Search Bar -->
            <div class="d-flex justify-content-center flex-grow-1">
                 <div style="width: 100%; max-width: 500px;">
                    @php
                        $filterOptions = [
                            ['label' => 'Archived', 'value' => 'archived']
                        ];
                        $groupByOptions = [
                            ['label' => 'Manager', 'value' => 'manager'],
                            ['label' => 'Location', 'value' => 'location'],
                            ['label' => 'Parent Department', 'value' => 'parent'],
                        ];
                    @endphp
                     <x-odoo-search-bar 
                         :fields="[['key' => 'name', 'label' => 'Department Name']]" 
                         action="{{ route('departments.index') }}" 
                         :filterOptions="$filterOptions"
                         :groupByOptions="$groupByOptions"
                         targetSelector=".department-grid-container"
                     />
                 </div>
            </div>

            <!-- Right: Action Menu -->
            <div class="flex-shrink-0">
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-gear"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)">
                                {{ __('Configuration') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="kanban-board card mb-0">
            <div class="card-body">
                <div class="department-grid-container">
                    @if(isset($isGrouped) && $isGrouped)
                        @foreach($departments as $groupKey => $groupItems)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h5 class="fw-bold text-dark border-bottom pb-2">
                                        <i class="fa fa-layer-group me-2 text-secondary"></i> {{ $groupKey }}
                                        <span class="badge bg-light text-dark border ms-2 rounded-pill">{{ count($groupItems) }}</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="row px-1">
                                @foreach($groupItems as $department)
                                    @include('pages.departments.partials.card', ['department' => $department])
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="row px-1">
                            @foreach($departments as $department)
                                @include('pages.departments.partials.card', ['department' => $department])
                            @endforeach
                            
                            @if($departments->isEmpty())
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">{{ __('No departments found') }}</p>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection




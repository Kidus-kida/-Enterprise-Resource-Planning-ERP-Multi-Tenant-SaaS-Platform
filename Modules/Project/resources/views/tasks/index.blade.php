@extends('layouts.app')

@push('page-styles')
    <style>
        /* Viewport Layout fixes */
        .content {
            height: calc(100vh - 64px); /* Subtract approximate header height */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Prevent window scroll */
            padding-bottom: 0 !important;
        }

        .board-view-header {
            flex: 0 0 auto;
            margin-bottom: 1rem !important;
        }

        /* Responsive spacing for filter form */
        .board-view-header .form-control {
            margin-bottom: 0;
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

        .kanban-cont {
            display: flex;
            overflow-x: auto;
            overflow-y: hidden;
            flex: 1;
            padding-bottom: 10px; /* Space for horizontal scrollbar */
            gap: 15px; /* Gap between columns */
        }

        .kanban-list {
            min-width: 300px;
            width: 300px;
            display: flex;
            flex-direction: column;
            height: 100%;
            background-color: #f8f9fa; /* Light background for column */
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .kanban-header {
            flex: 0 0 auto;
            padding: 10px 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        .kanban-wrap {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            min-height: 0; /* Enable scrolling in flex child */
        }

        /* Task Card Styling */
        .task-card {
            margin-bottom: 10px;
            border: 1px solid #e3e6f0;
            border-left: 4px solid transparent; /* For priority indicator */
            transition: all 0.2s ease-in-out;
            background: #fff;
            border-radius: 4px;
        }

        .task-card:last-child {
            margin-bottom: 0px;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .kanban-box {
            padding: 10px;
        }

        .task-card-header {
            margin-bottom: 8px;
        }

        .task-title {
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .task-title a {
            color: #333;
            text-decoration: none;
        }

        .task-title a:hover {
            color: #007bff;
        }

        .task-priority-indicator {
            width: 100%;
            height: 3px;
            border-radius: 2px;
            margin: 8px 0;
        }

        .task-card-footer {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .assigned-user-item .avatar-sm {
            width: 24px;
            height: 24px;
        }

        .kanban-task-action .dropdown-toggle::after { display: none; }
        .kanban-task-action .dropdown-toggle i {
            font-size: 1rem;
            color: #6c757d;
        }

        /* Scrollbar Styling */
        .kanban-cont::-webkit-scrollbar,
        .kanban-wrap::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .kanban-cont::-webkit-scrollbar-track,
        .kanban-wrap::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .kanban-cont::-webkit-scrollbar-thumb,
        .kanban-wrap::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .kanban-cont::-webkit-scrollbar-thumb:hover,
        .kanban-wrap::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Odoo Header Styles */
        .odoo-task-header {
            min-height: 50px;
            border-bottom: 1px solid #e3e6f0;
            background: #fff;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .odoo-search-bar .form-control {
            border-radius: 0;
            border-color: #ced4da;
        }
        
        .odoo-search-bar .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .odoo-search-bar .input-group-text {
            border-radius: 4px 0 0 4px;
            border-color: #ced4da;
        }
        
        /* Adjust content height since header is smaller */
        .content {
            height: calc(100vh - 60px); 
            display: flex;
            flex-direction: column;
            overflow: hidden; 
            padding-bottom: 0 !important;
            padding-top: 0 !important; /* Remove top padding to fit flush */
        }
    </style>
    <!-- Page Css -->
    <!-- /Page Css -->
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Odoo-style Compact Header -->
        <div class="odoo-task-header d-flex align-items-center mb-2 px-1 gap-2">
            <!-- Left: Title & New Button -->
            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                <h4 class="mb-0 fw-bold">{{ $project->name }}</h4>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm"
                    data-url="{{ route('project-tasks.create', ['project' => $project->id]) }}" data-ajax-modal="true"
                    data-size="md" data-title="{{ __('Add Task') }}">
                    {{ __('New') }}
                </a>
            </div>

            <!-- Center: Search Bar -->
            <div class="d-flex justify-content-center flex-grow-1">
                <div style="width: 100%; max-width: 500px;">
                    @php
                        $taskFilterOptions = [
                            ['label' => 'My Tasks', 'value' => 'my_tasks', 'key' => 'preset'],
                            ['label' => 'Unassigned', 'value' => 'unassigned', 'key' => 'preset'],
                            ['label' => 'Open', 'value' => 'open', 'key' => 'preset'],
                            ['label' => 'Closed', 'value' => 'closed', 'key' => 'preset'],
                        ];
                        $taskGroupByOptions = [
                            ['label' => 'Assignees', 'value' => 'assignees'],
                            ['label' => 'Stage', 'value' => 'stage'],
                            ['label' => 'Project', 'value' => 'project'],
                            ['label' => 'Priority', 'value' => 'priority'],
                        ];
                    @endphp
                    <x-odoo-search-bar 
                        :action="route('project.taskboard', ['id' => \Crypt::encrypt($project->id)])"
                        :fields="[
                            ['key' => 'person', 'label' => 'Assigned To'],
                            ['key' => 'startDate', 'label' => 'Start Date'],
                            ['key' => 'endDate', 'label' => 'End Date'],
                            ['key' => 'search', 'label' => 'Text']
                        ]"
                        :filterOptions="$taskFilterOptions"
                        :groupByOptions="$taskGroupByOptions"
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
                             <a href="javascript:void(0)" class="dropdown-item"
                                data-url="{{ route('task-boards.create', ['project_id' => $project->id]) }}" data-ajax-modal="true"
                                data-size="md" data-title="Add Column">
                                {{ __('Add Column') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('projects.show', ['project' => \Crypt::encrypt($project->id)]) }}" class="dropdown-item">
                                {{ __('Project Settings') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="kanban-board card mb-0">
            <div class="card-body">
                <div class="kanban-cont">
                    @foreach ($taskBoards as $board)
                        <div class="kanban-list">
                            <div class="kanban-header" style="background: {{ $board->color ?? '#42a5f5' }};">
                                <span class="status-title">{{ $board->name }}</span>
                                <div class="dropdown kanban-action">
                                    <a href="#" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="javascript:void(0)" class="dropdown-item"
                                            data-url="{{ route('task-boards.edit', ['task_board' => $board->id, 'project_id' => $project->id]) }}"
                                            data-ajax-modal="true" data-title="{{ __('Edit Task Board') }}"
                                            data-size="md">{{ __('Edit') }}</a>
                                        <a class="dropdown-item deleteBtn"
                                            data-route="{{ route('task-boards.destroy', ['task_board' => $board->id, 'project_id' => $project->id]) }}"
                                            data-title="{{ __('Delete Task Board') }}"
                                            data-question="{{ __('Are you sure you want to delete taskboard?') }}"
                                            href="javascript:void(0)">
                                            {{ __('Delete') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {{-- Use the already-filtered tasks from the controller's eager load --}}
                            <div class="kanban-wrap" data-board="{{ $board->id }}">
                                @foreach ($board->tasks as $task)
                                <div class="card panel task-card" data-id="{{ $task->priority }}" data-task="{{ $task->id }}" data-board="{{ $board->id }}">
                                    <div class="kanban-box">
                                        <div class="task-card-header d-flex justify-content-between align-items-start">
                                            <h6 class="task-title flex-grow-1 mb-0"><a href="{{ route('tasks.show', $task->id) }}">{{ $task->name }}</a></h6>
                                            <div class="dropdown kanban-task-action">
                                                <a href="#" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="javascript:void(0)" data-ajax-modal="true"
                                                        data-title="{{ __('Edit Task') }}"
                                                        data-url="{{ route('project-tasks.edit', $task->id) }}" data-size="md">
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <a class="dropdown-item deleteBtn"
                                                        data-route="{{ route('project-tasks.destroy', $task->id) }}"
                                                        data-title="{{ __('Delete Task') }}"
                                                        data-question="{{ __('Are you sure you want to delete Task?') }}"
                                                        href="javascript:void(0)">
                                                        {{ __('Delete') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap mt-2">
                                            @foreach($task->labels as $label)
                                                <span class="badge me-1" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                                            @endforeach
                                        </div>
                                        <div class="task-card-body">
                                            <div class="task-priority-indicator" style="background-color: {{ $board->color ?? '#42a5f5' }};"></div>
                                        </div>
                                        <div class="task-card-footer d-flex flex-column align-items-start mt-2">
                                            <span class="task-dates text-muted mb-1">
                                                <i class="fa-regular fa-clock me-1"></i>
                                                {{ format_date($task->startDate) }} - {{ format_date($task->endDate) }}
                                            </span>
                                            <div class="task-assigned-users">
                                                @forelse ($task->followers as $follower)
                                                    <div class="assigned-user-item d-flex align-items-center mb-1">
                                                        <img src="{{ !empty($follower->user->avatar) ? uploadedAsset($follower->user->avatar, 'users') : asset('images/user.jpg') }}"
                                                            class="avatar avatar-sm rounded-circle me-2" alt="{{ $follower->user->fullname }}">
                                                        <span class="text-muted">{{ explode(' ', $follower->user->fullname)[0] }}</span>
                                                    </div>
                                                @empty
                                                    <span class="text-muted fst-italic">{{ __('Unassigned') }}</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="add-new-task">
                                <a href="javascript:void(0);" data-ajax-modal="true"
                                    data-url="{{ route('project-tasks.create', ['project' => $project->id, 'board' => $board->id]) }}"
                                    data-size="md" data-title="{{ __('Add Task') }}">{{ __('Add New Task') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

@endsection


@push('page-scripts')
    <!-- Page Js -->
    <script type="module">
        var taskBoxWrapper = [].slice.call(document.querySelectorAll('.kanban-wrap'));
        for (var i = 0; i < taskBoxWrapper.length; i++) {
            new Sortable(taskBoxWrapper[i], {
                group: 'taskboard',
                handle: ".kanban-box",
                draggable: ".panel",
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                dataIdAttr: 'data-id',
                onEnd: function (event) {
                    var element = $(event.item)
                    var priority = event.newIndex
                    var taskId = element.data('task')
                    let taskBoard = $(event.to).data('board')
                    $.ajax({
                        url: "{{ route('project-task.update-dragged') }}",
                        type: "POST",
                        data: {
                            task: taskId,
                            priority: priority,
                            board: taskBoard,
                        },
                        success: function (e) {
                            if (e.success) {
                                Toastify({
                                    text: "{{ __('Task updated successfully') }}",
                                    className: "success",
                                }).showToast();
                            } else {
                                alert('something went wrong')
                            }
                        }
                    })
                },
            });
        }
    </script>
    <script>
        window.addEventListener('load', function() {
            $(function() {
                // Initialize Select2 for person filter
                $('#person-filter-select').select2({
                    placeholder: 'Select a person',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0 // Ensure search is enabled
                });
            });
        });
    </script>
    <!-- /Page Js -->
@endpush
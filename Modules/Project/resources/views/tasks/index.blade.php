@extends('layouts.app')

@push('page-styles')
    <style>
        .kanban-list {
            min-width: 300px; /* Ensure lists have a minimum width */
        }

        .task-card {
            margin-bottom: 10px;
            border-left: 4px solid transparent; /* For priority indicator */
            transition: all 0.2s ease-in-out;
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
            font-size: 1rem;
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
            height: 4px;
            border-radius: 2px;
            margin-bottom: 8px;
        }

        .task-card-footer {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .assigned-user-item .avatar-sm {
            width: 24px;
            height: 24px;
        }

        .kanban-task-action .dropdown-toggle::after {
            display: none; /* Hide default caret */
        }
        .kanban-task-action .dropdown-toggle i {
            font-size: 1rem;
            color: #6c757d;
        }

        /* Custom styles for larger, circular avatars */
        .pro-team-lead .avatar,
        .pro-team-members .avatar {
            width: 40px; /* Reduced size */
            height: 40px; /* Reduced size */
            border-radius: 50%; /* Ensure perfect circle */
            overflow: hidden; /* Clip content outside the circle */
        }

        .pro-team-lead .avatar img,
        .pro-team-members .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensure image fills the circle without distortion */
            border-radius: 50%; /* Redundant but good for safety */
        }
    </style>
    <!-- Page Css -->
    <!-- /Page Css -->
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ $project->name }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Task Board') }}
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row board-view-header">
            <div class="col-4">
                <div class="pro-teams">
                    <div class="pro-team-lead">
                        <h4>{{ __('Lead') }}</h4>
                        <div class="avatar-group">
                            <div class="avatar">
                                <img class="avatar-img rounded-circle border border-white"
                                    src="{{ !empty($project->leader->avatar) ? uploadedAsset($project->leader->avatar, 'users') : asset('images/user.jpg') }}"
                                    alt="{{ __('avatar') }}">
                            </div>
                        </div>
                    </div>
                    @php
                        $projectTeam = $project->team;
                    @endphp
                    @if (!empty($projectTeam) && $projectTeam->count())
                        <div class="pro-team-members">
                            <h4>{{ __('Team') }}</h4>
                            <div class="avatar-group">
                                @foreach ($projectTeam as $member)
                                    <div class="avatar">
                                        <img class="avatar-img rounded-circle border border-white"
                                            src="{{ !empty($member->user->avatar) ? uploadedAsset($member->user->avatar, 'users') : asset('images/user.jpg') }}"
                                            alt="{{ $member->user->fullname . ' avatar' }}">
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-8 text-end">
                <a href="javascript:void(0)" class="btn btn-white float-end ms-2"
                    data-url="{{ route('task-boards.create', ['project_id' => $project->id]) }}" data-ajax-modal="true"
                    data-size="md" data-title="Add Task Board">
                    <i class="fa-solid fa-plus"></i> {{ __('Create List') }}
                </a>
                <a href="{{ route('projects.show', ['project' => \Crypt::encrypt($project->id)]) }}"
                    class="btn btn-white float-end" data-bs-toggle="tooltip" title="View Project"><i
                        class="fa fa-link"></i></a>
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
                            @php
                                $tasks = $board->tasks()->orderBy('priority')->get()
                            @endphp
                            <div class="kanban-wrap" data-board="{{ $board->id }}">
                                @foreach ($tasks as $task)
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
    <!-- /Page Js -->
@endpush
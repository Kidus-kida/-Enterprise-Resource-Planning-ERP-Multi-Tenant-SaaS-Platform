@extends('layouts.app')

@push('page-styles')
<style>
    .task-detail-container {
        display: flex;
        gap: 2rem;
    }

    .task-main-content {
        flex: 3;
        min-width: 0;
    }

    .task-sidebar {
        flex: 1;
        min-width: 0;
    }

    .task-section {
        background-color: #fff;
        border: 1px solid #e3e3e3;
        border-radius: 0.25rem;
        margin-bottom: 1.5rem;
    }

    .task-section-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e3e3e3;
    }

    .task-section-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .task-section-body {
        padding: 1.25rem;
    }

    .task-title-header {
        margin-bottom: 1.5rem;
    }

    .task-title-header h3 {
        font-weight: 600;
        cursor: pointer;
    }

    .task-title-header .form-control {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .sidebar-section .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }

    .sidebar-section .form-select,
    .sidebar-section .form-control {
        font-size: 0.9rem;
    }

    .checklist .form-check {
        margin-bottom: 0.5rem;
    }

    .checklist .form-check-label {
        text-decoration: none;
    }

    .checklist .form-check-input:checked + .form-check-label {
        text-decoration: line-through;
        color: #888;
    }

    .attachment-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .attachment-list-item:last-child {
        border-bottom: none;
    }

    .discussion-feed .feed-item {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 1.5rem;
    }

    .discussion-feed .feed-item::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #007bff; /* Or use user avatar */
    }

    .discussion-feed .feed-user {
        font-weight: 600;
    }

    .discussion-feed .feed-date {
        font-size: 0.8rem;
        color: #6c757d;
        margin-left: 0.5rem;
    }

    .discussion-feed .feed-body {
        margin-top: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        padding: 0.75rem 1rem;
    }
    .sidebar-section .card-header {
        padding: 0;
        border-bottom: 1px solid #e3e3e3;
    }
    .sidebar-section .nav-tabs {
        border-bottom: none;
    }
    .sidebar-section .nav-tabs .nav-link {
        padding: 0.75rem 1rem; /* Adjusted padding for icons */
        border: none;
        border-bottom: 2px solid transparent;
        background-color: #f0f2f5; /* Default background for inactive tabs */
        margin-right: 2px; /* Small gap between tabs */
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .sidebar-section .nav-tabs .nav-link.active {
        border-bottom-color: #007bff;
        background-color: #e9ecef; /* Lighter background for active tab */
        color: #007bff; /* Active icon color */
    }
    .sidebar-section .nav-tabs .nav-link i {
        font-size: 1.2rem; /* Adjust icon size */
    }

    .history-feed .history-item {
        position: relative;
        padding-bottom: 1rem;
        border-left: 2px solid #e3e3e3;
        margin-left: 0.5rem;
    }
    .history-feed .history-item:last-child {
        border-left: none;
        padding-bottom: 0;
    }
    .history-feed .history-item::before {
        content: '';
        position: absolute;
        left: -7px; /* Adjust to center on the line */
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #007bff;
    }
    .history-feed .history-user {
        font-weight: 600;
        font-size: 0.9rem;
        margin-left: 1.5rem;
    }
    .history-feed .history-date {
        font-weight: normal;
        font-size: 0.8rem;
        color: #6c757d;
        margin-left: 0.5rem;
    }
    .history-feed .history-body {
        margin-left: 1.5rem;
        font-size: 0.9rem;
        color: #333;
    }

</style>
@endpush

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Task Detail') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('project.taskboard', ['id' => \Crypt::encrypt($task->project_id)]) }}">{{ __('Task Board') }}</a></li>
            <li class="breadcrumb-item active">{{ $task->name }}</li>
        </ul>
    </x-breadcrumb>
    <!-- /Page Header -->

    <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="task-detail-container">

            <!-- Main Content -->
            <div class="task-main-content">

                <!-- Task Title -->
                <div class="task-title-header" x-data="{ editing: false, title: '{{ $task->name }}' }">
                    <h3 x-show="!editing" @click="editing = true" title="Click to edit">{{ $task->name }}</h3>
                    <div x-show="editing">
                        <input type="text" class="form-control" name="name" x-model="title">
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                            <button type="button" class="btn btn-secondary btn-sm" @click="editing = false">{{ __('Cancel') }}</button>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="task-section" x-data="{ editingDescription: false }">
                    <div class="task-section-header d-flex justify-content-between align-items-center">
                        <h5>{{ __('Description') }}</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" x-show="!editingDescription" @click="editingDescription = true">{{ __('Edit') }}</button>
                    </div>
                    <div class="task-section-body">
                        <!-- View Mode -->
                        <div x-show="!editingDescription">
                            @if($task->description)
                                {!! $task->description !!}
                            @else
                                <p class="text-muted fst-italic">{{ __('No description provided. Click "Edit" to add one.') }}</p>
                            @endif
                        </div>

                        <!-- Edit Mode -->
                        <div x-show="editingDescription" style="display: none;">
                            <textarea name="description" id="editor" class="form-control">{{ $task->description }}</textarea>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save Description') }}</button>
                                <button type="button" class="btn btn-secondary btn-sm" @click="editingDescription = false">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Discussion Section -->
                <div class="task-section">
                    <div class="task-section-header">
                        <h5>{{ __('Discussion') }}</h5>
                    </div>
                    <div class="task-section-body">
                        <div class="discussion-feed">
                            @foreach($task->comments as $comment)
                            <div class="feed-item">
                                <div class="feed-user">{{ $comment->user->fullname }} <span class="feed-date">{{ $comment->created_at->diffForHumans() }}</span></div>
                                <div class="feed-body">
                                    <p>{{ $comment->message }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="input-group mt-4">
                            <input type="text" class="form-control" name="comment" placeholder="{{ __('Add a comment') }}">
                            <button type="submit" name="action" value="add_comment" class="btn btn-primary">{{ __('Comment') }}</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="task-sidebar">
                <div class="task-section sidebar-section">
                    <div class="card-header">
                        <ul class="nav nav-tabs nav-tabs-solid nav-justified">
                            <li class="nav-item"><a class="nav-link active" href="#task-details" data-bs-toggle="tab" title="{{ __('Details') }}"><i class="fa fa-clipboard-list"></i></a></li>
                            <li class="nav-item"><a class="nav-link" href="#task-attachments" data-bs-toggle="tab" title="{{ __('Attachments') }}"><i class="fa fa-paperclip"></i></a></li>
                            <li class="nav-item"><a class="nav-link" href="#task-history" data-bs-toggle="tab" title="{{ __('History') }}"><i class="fa fa-history"></i></a></li>
                        </ul>
                    </div>
                    <div class="task-section-body">
                        <div class="tab-content">
                            <div class="tab-pane show active" id="task-details">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('State') }}</label>
                                    <select class="form-select" name="project_task_board_id" onchange="this.form.submit()">
                                        @foreach($taskBoards as $board)
                                            <option value="{{ $board->id }}" @if($board->id == $task->project_task_board_id) selected @endif>{{ $board->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Priority') }}</label>
                                    <select class="form-select" name="priority" onchange="this.form.submit()">
                                        <option value="low" @if($task->priority == 'low') selected @endif>{{ __('Low') }}</option>
                                        <option value="medium" @if($task->priority == 'medium') selected @endif>{{ __('Medium') }}</option>
                                        <option value="high" @if($task->priority == 'high') selected @endif>{{ __('High') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Assigned To') }}</label>
                                    <select id="assigned_to_select" class="form-control" name="followers[]" multiple>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" @if($task->followers->contains('user_id', $employee->id)) selected @endif>{{ $employee->fullname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="tab-pane" id="task-attachments">
                                <ul class="list-unstyled">
                                    @foreach($task->getMedia('task_files') as $file)
                                    <li class="attachment-list-item">
                                        <span>
                                            <i class="fa fa-paperclip me-2"></i>
                                            <a href="{{ $file->getUrl() }}" target="_blank">{{ $file->name }}</a>
                                        </span>
                                        <a href="#" class="btn btn-sm text-danger" onclick="deleteFile({{ $file->id }})" title="Delete"><i class="fa fa-trash"></i></a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="input-group mt-3">
                                    <input type="file" class="form-control" name="file">
                                    <button type="submit" name="action" value="upload_file" class="btn btn-primary">{{ __('Upload') }}</button>
                                </div>
                            </div>
                            <div class="tab-pane" id="task-history">
                                <div class="history-feed">
                                    @forelse($task->history as $activity)
                                        <div class="history-item">
                                            <div class="history-user">
                                                {{ $activity->user->fullname ?? 'System' }}
                                                <span class="history-date">{{ $activity->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="history-body">
                                                @if($activity->field == 'title')
                                                    changed the title from <strong>{{ $activity->old_value }}</strong> to <strong>{{ $activity->new_value }}</strong>
                                                @elseif($activity->field == 'description')
                                                    updated the description.
                                                @elseif($activity->field == 'state')
                                                    moved this task from <strong>{{ $activity->old_value }}</strong> to <strong>{{ $activity->new_value }}</strong>
                                                @else
                                                    updated the {{ $activity->field }}
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted">{{ __('No history found.') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('page-scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/35.3.2/classic/ckeditor.js"></script>
<script>
    // The CKEditor is initialized by the global app.js file

    function updateSubtaskStatus(subtaskId, isChecked) {
        let form = document.querySelector('form');
        let statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'subtask_id';
        statusInput.value = subtaskId;
        form.appendChild(statusInput);

        let statusValueInput = document.createElement('input');
        statusValueInput.type = 'hidden';
        statusValueInput.name = 'subtask_status';
        statusValueInput.value = isChecked ? 'complete' : 'incomplete';
        form.appendChild(statusValueInput);

        form.submit();
    }

    function deleteFile(fileId) {
        let form = document.querySelector('form');
        let fileInput = document.createElement('input');
        fileInput.type = 'hidden';
        fileInput.name = 'delete_file_id';
        fileInput.value = fileId;
        form.appendChild(fileInput);

        form.submit();
    }

    $(document).ready(function() {
        $('#assigned_to_select').select2({
            placeholder: 'Select followers',
            width: '100%'
        });

        $('#assigned_to_select').on('change', function (e) {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush
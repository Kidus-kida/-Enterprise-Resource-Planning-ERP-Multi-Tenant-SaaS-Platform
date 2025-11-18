@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">

    <!-- Page Header -->
    <x-breadcrumb class="col">
        <x-slot name="title">{{ $task->name }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('project.taskboard', ['id' => \Crypt::encrypt($task->project_id)]) }}">{{ __('Task Board') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Task Detail') }}
            </li>
        </ul>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div x-data="{ editing: false, title: '{{ $task->name }}' }">
                                    <h5 class="card-title" x-show="!editing" @click="editing = true">{{ $task->name }}</h5>
                                    <div x-show="editing">
                                        <input type="text" class="form-control" name="name" x-model="title">
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">{{ __('Save') }}</button>
                                        <button type="button" class="btn btn-secondary btn-sm mt-2" @click="editing = false">{{ __('Cancel') }}</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea name="description" id="editor" class="form-control">{{ $task->description }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">{{ __('Save') }}</button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Checklist') }}</h5>
                                <div class="checklist">
                                    @foreach($task->subtasks as $subtask)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="subtask-{{ $subtask->id }}" @if($subtask->status == 'complete') checked @endif onchange="updateSubtaskStatus({{ $subtask->id }}, this.checked)">
                                        <label class="form-check-label" for="subtask-{{ $subtask->id }}">
                                            {{ $subtask->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="subtask_name" placeholder="{{ __('Add a sub-task') }}">
                                    <button type="submit" name="action" value="add_subtask" class="btn btn-primary">{{ __('Add') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Attachments') }}</h5>
                                <ul class="list-group list-group-flush">
                                    @foreach($task->getMedia('task_files') as $file)
                                    <li class="list-group-item">
                                        <a href="{{ $file->getUrl() }}" target="_blank">{{ $file->name }}</a>
                                        <a href="#" class="btn btn-danger btn-sm float-end" onclick="deleteFile({{ $file->id }})">{{ __('Delete') }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-footer">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="file">
                                    <button type="submit" name="action" value="upload_file" class="btn btn-primary">{{ __('Upload') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs nav-tabs-solid nav-justified">
                                    <li class="nav-item"><a class="nav-link active" href="#task-details" data-bs-toggle="tab">{{ __('Details') }}</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#task-activity" data-bs-toggle="tab">{{ __('Activity') }}</a></li>
                                </ul>
                            </div>
                            <div class="card-body">
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
                                            <select class="form-select" name="followers[]" multiple onchange="this.form.submit()">
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" @if($task->followers->contains('user_id', $employee->id)) selected @endif>{{ $employee->fullname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="task-activity">
                                        <div class="activity-feed">
                                            @foreach($task->comments as $comment)
                                            <div class="feed-item">
                                                <div class="feed-date">{{ $comment->created_at->diffForHumans() }}</div>
                                                <span class="feed-text">
                                                    <a href="#">{{ $comment->user->fullname }}</a> {{ __('added a comment') }}
                                                </span>
                                                <div class="feed-body">
                                                    <p>{{ $comment->comment }}</p>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="card-footer">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="comment" placeholder="{{ __('Add a comment') }}">
                                                <button type="submit" name="action" value="add_comment" class="btn btn-primary">{{ __('Comment') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/35.3.2/classic/ckeditor.js"></script>
<script>
            // The CKEditor is initialized by the global app.js file
            // ClassicEditor
            //     .create(document.querySelector('#editor'))
            //     .catch(error => {
            //         console.error(error);
            //     });

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
</script>
@endpush
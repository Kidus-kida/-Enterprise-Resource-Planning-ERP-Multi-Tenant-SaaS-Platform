@extends('layouts.app')

@push('page-styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .task-detail-container {
            display: flex;
            gap: 2rem;
        }
        .task-main-content { flex: 3; min-width: 0; }
        .task-sidebar { flex: 1; min-width: 0; }
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
        .task-section-header h5 { margin: 0; font-size: 1.1rem; font-weight: 600; }
        .task-section-body { padding: 1.25rem; }
        .task-title-header { margin-bottom: 1.5rem; }
        .task-title-header h3 { font-weight: 600; cursor: pointer; }
        .task-title-header .form-control { font-size: 1.5rem; font-weight: 600; }
        .sidebar-section .form-label { font-weight: 600; color: #333; margin-bottom: 0.25rem; }
        .sidebar-section .form-select, .sidebar-section .form-control { font-size: 0.9rem; }
        .attachment-list-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
        .attachment-list-item:last-child { border-bottom: none; }
        .discussion-feed .feed-item { position: relative; padding-left: 2rem; margin-bottom: 1.5rem; }
        .discussion-feed .feed-item::before { content: ''; position: absolute; left: 0.5rem; top: 0.25rem; width: 1rem; height: 1rem; border-radius: 50%; background-color: #007bff; }
        .discussion-feed .feed-user { font-weight: 600; }
        .discussion-feed .feed-date { font-size: 0.8rem; color: #6c757d; margin-left: 0.5rem; }
        .discussion-feed .feed-body { margin-top: 0.5rem; background-color: #f8f9fa; border-radius: 0.25rem; padding: 0.75rem 1rem; }
        .sidebar-section .card-header { padding: 0; border-bottom: 1px solid #e3e3e3; }
        .sidebar-section .nav-tabs { border-bottom: none; }
        .sidebar-section .nav-tabs .nav-link { padding: 0.75rem 1rem; border: none; border-bottom: 2px solid transparent; background-color: #f0f2f5; margin-right: 2px; display: flex; justify-content: center; align-items: center; }
        .sidebar-section .nav-tabs .nav-link.active { border-bottom-color: #007bff; background-color: #e9ecef; color: #007bff; }
        .sidebar-section .nav-tabs .nav-link i { font-size: 1.2rem; }
        .history-feed .history-item { position: relative; padding-bottom: 1rem; border-left: 2px solid #e3e3e3; margin-left: 0.5rem; }
        .history-feed .history-item:last-child { border-left: none; padding-bottom: 0; }
        .history-feed .history-item::before { content: ''; position: absolute; left: -7px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background-color: #fff; border: 2px solid #007bff; }
        .history-feed .history-user { font-weight: 600; font-size: 0.9rem; margin-left: 1.5rem; }
        .history-feed .history-date { font-weight: normal; font-size: 0.8rem; color: #6c757d; margin-left: 0.5rem; }
        .history-feed .history-body { margin-left: 1.5rem; font-size: 0.9rem; color: #333; }
        .ql-editor { min-height: 150px; }
    </style>
@endpush

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Task Detail') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('project.taskboard', ['id' => \Crypt::encrypt($task->project_id)]) }}">{{ __('Task Board') }}</a></li>
                <li class="breadcrumb-item active">{{ $task->name }}</li>
            </ul>
        </x-breadcrumb>

        <form id="task-update-form" action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="task-detail-container">
                <div class="task-main-content">
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

                    <div class="task-section" x-data="{ editingDescription: false }">
                        <div class="task-section-header d-flex justify-content-between align-items-center">
                            <h5>{{ __('Description') }}</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" x-show="!editingDescription" @click="editingDescription = true">{{ __('Edit') }}</button>
                        </div>
                        <div class="task-section-body">
                            <div x-show="!editingDescription">
                                @if($task->description) {!! $task->description !!} @else <p class="text-muted fst-italic">{{ __('No description provided.') }}</p> @endif
                            </div>
                            <div x-show="editingDescription" style="display: none;">
                                <div id="description-editor-container">{!! $task->description !!}</div>
                                <input type="hidden" name="description" id="description-editor-input">
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Save Description') }}</button>
                                    <button type="button" class="btn btn-secondary btn-sm" @click="editingDescription = false">{{ __('Cancel') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="task-section">
                        <div class="task-section-header"><h5>{{ __('Discussion') }}</h5></div>
                        <div class="task-section-body">
                            <div class="discussion-feed">
                                @foreach($task->comments as $comment)
                                    <div class="feed-item">
                                        <div class="feed-user">{{ $comment->user->fullname }} <span class="feed-date">{{ $comment->created_at->diffForHumans() }}</span></div>
                                        <div class="feed-body">{!! $comment->message !!}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <div id="comment-editor-container"></div>
                                <input type="hidden" name="comment" id="comment-editor-input">
                                <button type="submit" name="action" value="add_comment" class="btn btn-primary mt-2">{{ __('Comment') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

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
                                            @foreach($taskBoards as $board) <option value="{{ $board->id }}" @if($board->id == $task->project_task_board_id) selected @endif>{{ $board->name }}</option> @endforeach
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
                                            @foreach($employees as $employee) <option value="{{ $employee->id }}" @if($task->followers->contains('user_id', $employee->id)) selected @endif>{{ $employee->fullname }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Labels') }}</label>
                                        <select id="labels_select" class="form-control" name="labels[]" multiple>
                                            @foreach($labels as $label) <option value="{{ $label->name }}" @if($task->labels->contains('id', $label->id)) selected @endif>{{ $label->name }}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane" id="task-attachments">
                                    <ul class="list-unstyled">
                                        @foreach($task->getMedia('task_files') as $file)
                                            <li class="attachment-list-item">
                                                <span><i class="fa fa-paperclip me-2"></i><a href="{{ $file->getUrl() }}" target="_blank">{{ $file->name }}</a></span>
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
                                                <div class="history-user">{{ $activity->user->fullname ?? 'System' }} <span class="history-date">{{ $activity->created_at->diffForHumans() }}</span></div>
                                                <div class="history-body">
                                                    @if($activity->field == 'title') changed the title from <strong>{{ $activity->old_value }}</strong> to <strong>{{ $activity->new_value }}</strong>
                                                    @elseif($activity->field == 'description') updated the description.
                                                    @elseif($activity->field == 'state') moved this task from <strong>{{ $activity->old_value }}</strong> to <strong>{{ $activity->new_value }}</strong>
                                                    @else updated the {{ $activity->field }} @endif
                                                </div>
                                            </div>
                                        @empty <p class="text-muted">{{ __('No history found.') }}</p> @endforelse
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
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    function updateSubtaskStatus(subtaskId, isChecked) {
        let form = document.querySelector('form');
        let statusInput = document.createElement('input');
        statusInput.type = 'hidden'; statusInput.name = 'subtask_id'; statusInput.value = subtaskId;
        form.appendChild(statusInput);
        let statusValueInput = document.createElement('input');
        statusValueInput.type = 'hidden'; statusValueInput.name = 'subtask_status'; statusValueInput.value = isChecked ? 'complete' : 'incomplete';
        form.appendChild(statusValueInput);
        form.submit();
    }

    function deleteFile(fileId) {
        let form = document.querySelector('form');
        let fileInput = document.createElement('input');
        fileInput.type = 'hidden'; fileInput.name = 'delete_file_id'; fileInput.value = fileId;
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

        $('#labels_select').select2({
            placeholder: 'Select labels',
            width: '100%',
            tags: true
        });
        $('#labels_select').on('change', function (e) {
            $(this).closest('form').submit();
        });

        const toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'], ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            ['link', 'image'], ['clean']
        ];

        function quillImageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = () => {
                const file = input.files[0];
                if (/^image\//.test(file.type)) {
                    const formData = new FormData();
                    formData.append('upload', file);

                    fetch('{{ route("tasks.upload-image") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.url) {
                            const range = this.quill.getSelection(true);
                            this.quill.insertEmbed(range.index, 'image', result.url);
                        } else if (result.error) {
                            alert(result.error.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Image upload failed.');
                    });
                } else {
                    alert('You can only upload images.');
                }
            };
        }

        let descQuill, commentQuill;

        if (document.querySelector('#description-editor-container')) {
            descQuill = new Quill('#description-editor-container', {
                modules: { toolbar: toolbarOptions },
                theme: 'snow'
            });
            descQuill.getModule('toolbar').addHandler('image', quillImageHandler);
        }

        if (document.querySelector('#comment-editor-container')) {
            commentQuill = new Quill('#comment-editor-container', {
                modules: { toolbar: toolbarOptions },
                theme: 'snow',
                placeholder: 'Add a comment...'
            });
            commentQuill.getModule('toolbar').addHandler('image', quillImageHandler);
        }

        $('#task-update-form').on('submit', function() {
            if (descQuill) {
                $('#description-editor-input').val(descQuill.root.innerHTML);
            }
            if (commentQuill) {
                // Only set comment value if it's not empty to avoid submitting empty comments
                const commentContent = commentQuill.root.innerHTML;
                if (commentQuill.getLength() > 1) { // Quill length is 1 for an empty editor
                    $('#comment-editor-input').val(commentContent);
                }
            }
        });
    });
</script>
@endpush
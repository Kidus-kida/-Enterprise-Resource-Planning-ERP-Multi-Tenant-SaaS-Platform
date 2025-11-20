<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Project\Models\Task;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Facades\Crypt;
use Modules\Project\Models\Project;
use Modules\Project\Models\TaskFollower;
use Modules\Project\Models\SubTask;
use Modules\Project\Models\TaskComment;
use Illuminate\Support\Facades\Storage;
use Modules\Project\Models\ProjectTaskBoard;
use Modules\Project\Models\TaskHistory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Illuminate\Support\Facades\Mail;
use Modules\Project\Mail\UserAssignedToTask;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('project::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('project::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Task $task)
    {
        $pageTitle = __('Task Detail');
        $taskBoards = $task->project->taskBoard;
        $employees = User::where('is_active', true)->where('type', UserType::EMPLOYEE)->get();
        $task->load('history.user'); // Eager load history and the user who made the change
        return view('project::tasks.show', compact('task', 'pageTitle', 'taskBoards', 'employees'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('project::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param Task $task
     * @return Renderable
     */
    public function update(Request $request, Task $task)
    {
        if ($request->has('delete_file_id')) {
            $file = Media::find($request->delete_file_id);
            $file->delete();
            return redirect()->route('tasks.show', $task->id);
        }

        if ($request->has('subtask_id')) {
            $subtask = SubTask::find($request->subtask_id);
            $subtask->update(['status' => $request->subtask_status]);
            return redirect()->route('tasks.show', $task->id);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'project_task_board_id' => 'sometimes|required|integer|exists:project_task_boards,id',
            'priority' => 'sometimes|required|string|in:low,medium,high',
            'followers' => 'sometimes|array',
            'followers.*' => 'integer|exists:users,id',
            'description' => 'sometimes|nullable|string',
        ]);

        // Track name change
        if ($request->has('name') && $task->name != $request->name) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'field' => 'title',
                'old_value' => $task->name,
                'new_value' => $request->name
            ]);
        }

        // Track description change
        if ($request->has('description') && $task->description != $request->description) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'field' => 'description',
                'old_value' => 'Previous description',
                'new_value' => 'New description'
            ]);
        }

        // Track state change
        if ($request->has('project_task_board_id') && $task->project_task_board_id != $request->project_task_board_id) {
            $old_board_name = $task->taskBoard->name ?? 'N/A';
            $new_board_name = ProjectTaskBoard::find($request->project_task_board_id)->name ?? 'N/A';
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'field' => 'state',
                'old_value' => $old_board_name,
                'new_value' => $new_board_name
            ]);
        }

        $task->update($request->except('followers'));

        if ($request->has('followers')) {
            $existingFollowerIds = $task->followers()->pluck('user_id')->toArray();
            $newFollowerIds = $request->followers;

            // Add new followers
            $addedFollowerIds = array_diff($newFollowerIds, $existingFollowerIds);
            foreach ($addedFollowerIds as $followerId) {
                TaskFollower::create([
                    'task_id' => $task->id,
                    'user_id' => $followerId,
                ]);

                $user = User::find($followerId);

                Mail::to($user)->send(new UserAssignedToTask($task, $user));
            }

            // Remove old followers
            $removedFollowerIds = array_diff($existingFollowerIds, $newFollowerIds);
            if (!empty($removedFollowerIds)) {
                $task->followers()->whereIn('user_id', $removedFollowerIds)->delete();
            }
        }

        if ($request->has('subtask_name')) {
            SubTask::create([
                'task_id' => $task->id,
                'name' => $request->subtask_name,
                'status' => 'incomplete',
            ]);
        }

        if ($request->hasFile('file')) {
            $task->addMediaFromRequest('file')->toMediaCollection('task_files');
        }

        if ($request->filled('comment')) {
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'message' => $request->comment,
            ]);
        }

        return redirect()->route('tasks.show', $task->id);
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $path = $request->file('upload')->store('public/uploads');
            $url = Storage::url($path);

            return response()->json([
                'url' => $url
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => [
                    'message' => $e->errors()['upload'][0] ?? 'A validation error occurred.'
                ]
            ], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('CKEditor image upload failed: ' . $e->getMessage());

            return response()->json([
                'error' => [
                    'message' => 'The image upload failed on the server. Please check server logs.'
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Project\Models\Task;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TaskFileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $task->addMediaFromRequest('file')->toMediaCollection('task_files');

        return redirect()->route('tasks.show', $task->id);
    }

    /**
     * Remove the specified resource from storage.
     * @param Media $file
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Media $file)
    {
        $file->delete();

        return back();
    }
}

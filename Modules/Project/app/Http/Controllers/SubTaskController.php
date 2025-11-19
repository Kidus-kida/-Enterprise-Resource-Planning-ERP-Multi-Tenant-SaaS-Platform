<?php

namespace Modules\Project\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Project\Models\SubTask;
use Modules\Project\Models\Task;

class SubTaskController extends Controller
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
            'name' => 'required|string',
        ]);

        SubTask::create([
            'task_id' => $task->id,
            'name' => $request->name,
            'status' => 'incomplete',
        ]);

        return redirect()->route('tasks.show', $task->id);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param SubTask $subtask
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, SubTask $subtask)
    {
        $request->validate([
            'status' => 'required|string|in:complete,incomplete',
        ]);

        $subtask->update([
            'status' => $request->status,
        ]);

        return redirect()->route('tasks.show', $subtask->task_id);
    }
}

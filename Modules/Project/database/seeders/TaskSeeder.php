<?php

namespace Modules\Project\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Project\Models\Task;
use Modules\Project\Models\Project;
use Modules\Project\Models\TaskBoard;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $projects = Project::all();
        $taskBoards = TaskBoard::all();
        $users = User::where('type', 'employee')->get();

        if ($projects->count() > 0 && $taskBoards->count() > 0 && $users->count() > 0) {
            $tasks = [
                ['title' => 'Setup Project Environment', 'priority' => 'High', 'status' => 'Done'],
                ['title' => 'Design Database Schema', 'priority' => 'Medium', 'status' => 'In Progress'],
                ['title' => 'Create User Interface', 'priority' => 'Normal', 'status' => 'To Do'],
                ['title' => 'Write Unit Tests', 'priority' => 'Low', 'status' => 'To Do'],
                ['title' => 'Deploy to Production', 'priority' => 'High', 'status' => 'Review'],
            ];

            foreach ($tasks as $taskData) {
                Task::create([
                    'title' => $taskData['title'],
                    'description' => 'Sample task description for ' . $taskData['title'],
                    'project_id' => $projects->random()->id,
                    'task_board_id' => $taskBoards->random()->id,
                    'assigned_to' => $users->random()->id,
                    'priority' => $taskData['priority'],
                    'status' => $taskData['status'],
                    'due_date' => now()->addDays(rand(1, 30)),
                    'created_by' => $users->random()->id,
                ]);
            }
        }
    }
}
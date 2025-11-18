<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Project\Models\TaskBoard;
use App\Models\User;

class TaskBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure you have at least one user in the users table
        $user = User::first();

        if (!$user) {
            $this->command->info('No users found. Please seed the users table first.');
            return;
        }

        $boards = [
            ['name' => 'Backlog', 'color' => '#6c757d', 'priority' => 1],
            ['name' => 'To Do', 'color' => '#0d6efd', 'priority' => 2],
            ['name' => 'In Progress', 'color' => '#ffc107', 'priority' => 3],
            ['name' => 'Review', 'color' => '#fd7e14', 'priority' => 4],
            ['name' => 'Done', 'color' => '#198754', 'priority' => 5],
        ];

        foreach ($boards as $board) {
            TaskBoard::create([
                'name' => $board['name'],
                'color' => $board['color'],
                'priority' => $board['priority'],
                'created_by' => $user->id,
            ]);
        }
    }
}

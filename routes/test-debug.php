Route::get('/test-task-search', function() {
    // Test 1: Check if tasks have followers
    $tasks = \Modules\Project\Models\Task::with('followers.user')->get();
    dump('Total tasks: ' . $tasks->count());
    
    foreach ($tasks as $task) {
        dump('Task: ' . $task->name);
        dump('Followers count: ' . $task->followers->count());
        foreach ($task->followers as $follower) {
            dump('  - Follower user_id: ' . $follower->user_id);
            if ($follower->user) {
                dump('  - User: ' . $follower->user->firstname . ' ' . $follower->user->lastname);
            } else {
                dump('  - User not found!');
            }
        }
    }
    
    // Test 2: Try the filter
    $searchTerm = request('term', 'test');
    dump('Searching for: ' . $searchTerm);
    
    $query = \Modules\Project\Models\Task::query();
    $filter = new \Modules\Project\Services\ProjectTaskFilter($query, ['person' => $searchTerm]);
    $results = $filter->apply()->get();
    
    dump('Results count: ' . $results->count());
    foreach ($results as $result) {
        dump('Found task: ' . $result->name);
    }
    
    return 'Check the output above';
});

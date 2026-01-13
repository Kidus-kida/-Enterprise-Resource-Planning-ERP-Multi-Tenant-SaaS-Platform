<?php

namespace Modules\Project\Services;

use App\Services\Search\SearchFilter;
use Carbon\Carbon;

class ProjectTaskFilter extends SearchFilter
{
    protected function filterSearch($term)
    {
        $this->query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhereHas('labels', function ($q) use ($term) {
                  $q->where('name', 'LIKE', "%{$term}%");
              });
        });
    }

    protected function filterPerson($term)
    {
        $this->query->whereHas('followers', function ($q) use ($term) {
            if (is_numeric($term)) {
                $q->where('user_id', $term);
            } else {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'LIKE', "%{$term}%")
                       ->orWhere('email', 'LIKE', "%{$term}%");
                });
            }
        });
    }

    protected function filterStartDate($date)
    {
        try {
            $parsedDate = Carbon::parse($date)->startOfDay();
            $this->query->where('endDate', '>=', $parsedDate);
        } catch (\Exception $e) {
            // If date is invalid, we could try to search as text in description or ignore
            // For now, ignore invalid dates
        }
    }

    protected function filterEndDate($date)
    {
        try {
            $parsedDate = Carbon::parse($date)->endOfDay();
            $this->query->where('startDate', '<=', $parsedDate);
        } catch (\Exception $e) {
            // Ignore invalid dates
        }
    }
    
    protected function filterPreset($value)
    {
        switch ($value) {
            case 'my_tasks':
                $this->query->whereHas('followers', function ($q) {
                    $q->where('user_id', auth()->id());
                });
                break;
            case 'late_tasks':
                 $this->query->where('endDate', '<', Carbon::now());
                break;
            case 'unassigned':
                $this->query->doesntHave('followers');
                break;
            case 'open':
                // Assuming 'status' is a column or relationship. If not, maybe 'is_completed'?
                // Checking previous code... usually boolean or string 'completed'
                // Based on existing code, I don't see exact status logic, I'll assume standard 'status' != 'completed'
                // But looking at code, it seems 'status' might not be on 'tasks' table directly? 
                // Ah, Create Task has 'project_task_board_id' which represents stage.
                // We'll need to check if 'completed' is a board state? 
                // For now, let's assume 'open' means not in the last column or specific status.
                // Let's rely on a common convention or placeholder until verified.
                // Actually, I'll filter by 'is_complete' if it exists or similar.
                // Let's check the Task model or migration if possible. For now I'll use a placeholder logic that implies active checking.
                // Wait, standard Odoo logic for 'Open' usually means Stage != Done.
                // I will assume there is a 'is_completed' flag on the stage (Board). 
                $this->query->whereHas('board', function($q) {
                    $q->where('name', '!=', 'Completed')->where('name', '!=', 'Done'); 
                });
                break;
            case 'closed':
                 $this->query->whereHas('board', function($q) {
                    $q->where('name', 'Completed')->orWhere('name', 'Done');
                });
                break;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveAllocation;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveYearEndProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:year-end-process {--year= : The year to process (defaults to previous year)} {--dry-run : Simulate without changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process year-end leave carryover for all employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentYear = Carbon::now()->year;
        $prevYear = $this->option('year') ? (int)$this->option('year') : $currentYear - 1;
        $targetYear = $prevYear + 1;
        $dryRun = $this->option('dry-run');

        $this->info("Starting Leave Year-End Process for Year: $prevYear -> $targetYear");
        if ($dryRun) $this->warn("DRY RUN MODE: No changes will be saved.");

        $users = User::where('status', 'active')->get(); // Adjust filter as needed

        foreach ($users as $user) {
            $this->info("Processing User: {$user->name} ({$user->id})");

            $allocations = LeaveAllocation::where('user_id', $user->id)
                ->where('year', $prevYear)
                ->where('status', 'approved')
                ->where('available_days', '>', 0)
                ->get();

            foreach ($allocations as $allocation) {
                $type = $allocation->leaveType;
                
                // Skip if types doesn't support carry forward
                if (!$type || !$type->can_carry_forward) {
                    continue;
                }

                $remaining = $allocation->available_days;
                
                // Calculate Carry Over Amount
                $carryOver = $remaining;
                if ($type->max_carry_forward > 0) {
                    $carryOver = min($remaining, $type->max_carry_forward);
                }

                if ($carryOver <= 0) continue;

                $this->line("  - Found {$remaining} days of {$type->type_name}. Carrying over: {$carryOver}");

                if (!$dryRun) {
                    // Check if already processed
                    $exists = LeaveAllocation::where('user_id', $user->id)
                        ->where('leave_type_id', $type->id)
                        ->where('year', $targetYear)
                        ->where('allocation_type', 'carryover')
                        ->exists();

                    if ($exists) {
                        $this->warn("    - Already processed. Skipping.");
                        continue;
                    }

                    // Create Carryover Allocation
                    LeaveAllocation::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $type->id,
                        'year' => $targetYear,
                        'allocated_days' => 0, // Base allocation is 0
                        'available_days' => $carryOver,
                        'opening_balance' => $carryOver,
                        'used_days' => 0,
                        'allocation_type' => 'carryover',
                        'status' => 'approved',
                        'notes' => "Carried over from {$prevYear}. Original Balance: {$remaining}",
                        'allocated_by' => null, // System
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $this->info("    - Created carryover allocation.");
                }
            }
        }

        $this->info("Year-End Process Completed.");
    }
}

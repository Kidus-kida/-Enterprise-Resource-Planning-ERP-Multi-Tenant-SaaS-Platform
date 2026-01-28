<?php

namespace App\Jobs;

use App\Models\AttendanceTimestamp;
use App\Services\AttendancePolicyEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluateAttendancePolicy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * Number of seconds to wait before retrying.
     */
    public $backoff = 5;

    /**
     * Timeout for the job (seconds)
     */
    public $timeout = 15;

    protected int $timestampId;
    protected string $eventType; // 'clock_in' or 'clock_out'

    /**
     * Create a new job instance.
     */
    public function __construct(int $timestampId, string $eventType)
    {
        $this->timestampId = $timestampId;
        $this->eventType = $eventType;
    }

    /**
     * Execute the job.
     * 
     * CRITICAL: This job's failure does NOT invalidate attendance records.
     * Attendance records are already saved before this job runs.
     * Policy evaluation is informational only.
     */
    public function handle(): void
    {
        $timestamp = AttendanceTimestamp::find($this->timestampId);

        if (!$timestamp) {
            Log::warning("Attendance timestamp {$this->timestampId} not found for policy evaluation - possibly deleted");
            return;
        }

        try {
            $policyEngine = new AttendancePolicyEngine();

            if ($this->eventType === 'clock_in') {
                $violations = $policyEngine->evaluateClockIn($timestamp);
            } else {
                $violations = $policyEngine->evaluateClockOut($timestamp);
            }

            if (!empty($violations)) {
                Log::info("✓ Policy evaluation complete for timestamp {$this->timestampId}", [
                    'event_type' => $this->eventType,
                    'violation_count' => count($violations),
                    'violations' => array_column($violations, 'policy_type'),
                ]);

                // Optional: Send notification to employee or HR
                // This can be implemented later without affecting attendance validity
            } else {
                Log::info("✓ No policy violations for timestamp {$this->timestampId}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to evaluate policy for timestamp {$this->timestampId}: " . $e->getMessage());
            
            // If this is not the final attempt, throw to trigger retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
            
            // Final attempt failed - gracefully degrade
            Log::warning("⚠ Policy evaluation failed after {$this->tries} attempts for timestamp {$this->timestampId} - attendance record remains valid");
        }
    }

    /**
     * Handle a job failure.
     * 
     * CRITICAL: Attendance record remains valid even if this job fails.
     * Policy evaluation is informational - not blocking.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("⚠ Policy evaluation job failed permanently for timestamp {$this->timestampId}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'event_type' => $this->eventType,
        ]);

        // Attendance record remains valid - no action taken
        // Admin can manually review if needed
    }
}

<?php

namespace App\Jobs;

use App\Models\AttendanceTimestamp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResolveAttendanceLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Number of seconds to wait before retrying.
     */
    public $backoff = [10, 30, 60]; // 10s, 30s, 60s

    /**
     * Timeout for the job (seconds)
     */
    public $timeout = 30;

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public $failOnTimeout = true;

    protected int $timestampId;
    protected ?float $latitude;
    protected ?float $longitude;
    protected string $locationType; // 'clock_in' or 'clock_out'

    /**
     * Create a new job instance.
     */
    public function __construct(int $timestampId, ?float $latitude, ?float $longitude, string $locationType = 'clock_in')
    {
        $this->timestampId = $timestampId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->locationType = $locationType;
    }

    /**
     * Execute the job.
     * 
     * CRITICAL: This job's failure does NOT invalidate attendance records.
     * Attendance records are already saved before this job runs.
     */
    public function handle(): void
    {
        if (!$this->latitude || !$this->longitude) {
            Log::info("No GPS coordinates provided for timestamp {$this->timestampId} - skipping location resolution");
            return;
        }

        $timestamp = AttendanceTimestamp::find($this->timestampId);

        if (!$timestamp) {
            Log::warning("Attendance timestamp {$this->timestampId} not found - possibly deleted");
            return;
        }

        try {
            $locationName = $this->getLocationNameFromCoords($this->latitude, $this->longitude);

            if ($locationName) {
                // Update the appropriate location field
                $updateData = [];
                if ($this->locationType === 'clock_in') {
                    $updateData['location'] = $locationName;
                } else {
                    $updateData['co_location'] = $locationName;
                }

                $timestamp->update($updateData);

                Log::info("✓ Resolved location for timestamp {$this->timestampId}: {$locationName}");
            } else {
                // Graceful degradation: Location remains null but record is valid
                Log::warning("⚠ Could not resolve location for timestamp {$this->timestampId} - attendance record remains valid");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the job on final attempt
            Log::error("Failed to resolve location for timestamp {$this->timestampId}: " . $e->getMessage());
            
            // If this is not the final attempt, throw to trigger retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
            
            // Final attempt failed - gracefully degrade
            Log::warning("⚠ Location resolution failed after {$this->tries} attempts for timestamp {$this->timestampId} - attendance record remains valid");
        }
    }

    /**
     * Get location name from GPS coordinates
     * 
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @return string|null Location name
     * @throws \Exception When API call fails
     */
    private function getLocationNameFromCoords(float $lat, float $lng): ?string
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'MD Code Inc. ERP/1.0 (https://mdcodeinc.com)'
            ])
            ->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

        if ($response->successful()) {
            return $response->json()['display_name'] ?? null;
        }

        // Throw exception to trigger retry
        throw new \Exception("OpenStreetMap API returned status: " . $response->status());
    }

    /**
     * Handle a job failure.
     * 
     * CRITICAL: Attendance record remains valid even if this job fails.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("⚠ Location resolution job failed permanently for timestamp {$this->timestampId}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_type' => $this->locationType,
        ]);

        // Optional: Store failure in a separate table for admin review
        // FailedLocationResolution::create([...]);

        // Attendance record remains valid - no action taken
    }
}

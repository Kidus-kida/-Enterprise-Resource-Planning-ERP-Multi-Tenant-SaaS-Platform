<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceTimestamp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class EmployeeAttendanceController extends Controller
{

    public function clockIn(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric'
            ]);

            // Get authenticated user
            // $user = Auth::user();
            // if (!$user) {
            //     throw new \RuntimeException('User not authenticated');
            // }

            $locationName = null;

            // Handle location lookup if coordinates provided
            if ($request->latitude && $request->longitude) {
                try {
                    $locationName = $this->getLocationNameFromCoords(
                        $request->latitude,
                        $request->longitude
                    );
                } catch (\Exception $e) {
                    // Log but continue without location name
                    \Log::warning('Location lookup failed: ' . $e->getMessage());
                }
            }

            // Database transaction for atomic operations
            return DB::transaction(function () use ($locationName, $request) {
                // Find or create today's attendance record
                $todayAttendance = Attendance::firstOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'startDate' => Carbon::today()->format('Y-m-d')
                    ],
                    [
                        'startDate' => now(),
                        'endDate' => null,
                    ]
                );

                // Create timestamp record
                $timestamp = new AttendanceTimestamp([
                    'project_id' => 1,
                    'startTime' => now(),
                    'endTime' => null,
                    'location' => $locationName ?? null,
                    'billable' => false,
                    'ip' => $request->ip() ?? null,
                ]);

                $todayAttendance->timestamps()->save($timestamp);

                return response()->json([
                    'success' => true,
                    'message' => 'Clock-in successful',
                    'timestamp_id' => Crypt::encrypt($timestamp->id),
                    'clocked_in' => true,
                    'data' => [
                        'time' => now()->toDateTimeString(),
                        'location' => $timestamp->location
                    ]
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during clock-in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database operation failed',
                'error_code' => 'DB_OPERATION_FAILED'
            ], 500);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'AUTH_ERROR'
            ], 401);
        } catch (\Exception $e) {
            \Log::error('Unexpected error during clock-in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'system_message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function clockOut(Request $request, $timestampId)
    {
        try {
            $timestampId = Crypt::decrypt($timestampId);
            $timestamp = AttendanceTimestamp::findOrFail($timestampId);

            $locationName = null;
            if ($request->latitude && $request->longitude) {
                $locationName = $this->getLocationNameFromCoords($request->latitude, $request->longitude);
            }

            $timestamp->attendance->update([
                'endDate' => now(),
            ]);

            $timestamp->update([
                'endTime' => now(),
                'co_location' => $locationName ?? null,
            ]);

            return response()->json([
                'message' => 'Clock-out successful',
                'clocked_in' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function getClockInStatus(Request $request)
    {
        $user = Auth::user();
        $todayClockin = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $response = ['clocked_in' => false];

        if ($todayClockin) {
            $latestClockin = $todayClockin->timestamps()
                ->latest()
                ->whereNull('endTime')
                ->first();

            if ($latestClockin) {
                $response = [
                    'clocked_in' => true,
                    'timestamp_id' => Crypt::encrypt($latestClockin->id),
                    'time_started' => $latestClockin->startTime,
                    'total_hours' => Carbon::now()->diff($latestClockin->startTime)->h,
                ];
            }
        }

        return response()->json($response);
    }

    private function getLocationNameFromCoords($lat, $lng)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'TewosSmartHR/1.0 (https://smarthr.tewostechsolutions.com)'
        ])->get("https://nominatim.openstreetmap.org/reverse", [
            'format' => 'json',
            'lat' => $lat,
            'lon' => $lng,
            'zoom' => 18,
            'addressdetails' => 1,
        ]);

        if ($response->successful()) {
            return $response->json()['display_name'] ?? null;
        }

        return null;
    }
}

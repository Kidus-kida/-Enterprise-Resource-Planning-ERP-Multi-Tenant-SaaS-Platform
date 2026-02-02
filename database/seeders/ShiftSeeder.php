<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Seed default shifts (OPTIONAL - only created if needed)
     * 
     * These are examples. Users can create their own shifts.
     * If no shifts are created, system defaults to flexible attendance.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Standard Day Shift',
                'code' => 'DAY',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'grace_period_minutes' => 15,
                'is_active' => true,
                'description' => 'Standard 8-hour day shift (8 AM - 5 PM)',
                'work_days' => [1, 2, 3, 4, 5], // Monday to Friday
            ],
            [
                'name' => 'Morning Shift',
                'code' => 'MORNING',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Early morning shift (6 AM - 2 PM)',
                'work_days' => [1, 2, 3, 4, 5],
            ],
            [
                'name' => 'Evening Shift',
                'code' => 'EVENING',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Evening shift (2 PM - 10 PM)',
                'work_days' => [1, 2, 3, 4, 5],
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'grace_period_minutes' => 10,
                'is_active' => true,
                'description' => 'Overnight shift (10 PM - 6 AM)',
                'work_days' => [1, 2, 3, 4, 5],
            ],
            [
                'name' => 'Flexible Hours',
                'code' => 'FLEXIBLE',
                'start_time' => '00:00:00',
                'end_time' => '23:59:59',
                'grace_period_minutes' => 0,
                'is_active' => true,
                'description' => 'Flexible working hours - no fixed schedule',
                'work_days' => [1, 2, 3, 4, 5, 6, 7], // All days
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::updateOrCreate(
                ['code' => $shiftData['code']],
                $shiftData
            );
        }
    }
}

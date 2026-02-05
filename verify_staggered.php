<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Shift;
use App\Models\UserShift;
use App\Models\ShiftRotation;
use App\Models\ShiftRotationStep;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// setup
DB::table('shift_overrides')->truncate();
DB::table('user_shift_rotations')->truncate();
DB::table('shift_rotation_steps')->truncate();
DB::table('shift_rotations')->truncate();
DB::table('user_shifts')->truncate();

// 1. Create 3 Shifts
$s1 = Shift::create(['name' => 'Morning', 'code' => 'M'.time(), 'start_time' => '08:00', 'end_time' => '16:00', 'is_active' => 1]);
$s2 = Shift::create(['name' => 'Afternoon', 'code' => 'A'.time(), 'start_time' => '16:00', 'end_time' => '00:00', 'is_active' => 1]);
$s3 = Shift::create(['name' => 'Night', 'code' => 'N'.time(), 'start_time' => '00:00', 'end_time' => '08:00', 'is_active' => 1]);

// 2. Create Rotation Plan (Morning -> Afternoon -> Night)
$rot = ShiftRotation::create([
    'company_id' => 1,
    'name' => 'Full 24/7 Cycle',
    'frequency_type' => 'weekly',
    'frequency_interval' => 1,
    'start_date' => Carbon::now()->startOfWeek(),
    'is_active' => 1
]);

ShiftRotationStep::create(['shift_rotation_id' => $rot->id, 'shift_id' => $s1->id, 'step_order' => 1]);
ShiftRotationStep::create(['shift_rotation_id' => $rot->id, 'shift_id' => $s2->id, 'step_order' => 2]);
ShiftRotationStep::create(['shift_rotation_id' => $rot->id, 'shift_id' => $s3->id, 'step_order' => 3]);

// 3. Create 2 Users with different Base Shifts
$u1 = User::withoutGlobalScopes()->first(); // Assumed exists
$u2 = User::withoutGlobalScopes()->where('id', '!=', $u1->id)->first(); // Assumed exists

UserShift::withoutGlobalScopes()->create(['user_id' => $u1->id, 'shift_id' => $s1->id, 'effective_from' => '2020-01-01', 'is_active' => 1]);
UserShift::withoutGlobalScopes()->create(['user_id' => $u2->id, 'shift_id' => $s2->id, 'effective_from' => '2020-01-01', 'is_active' => 1]);

echo "TESTING AUTOMATIC STAGGERED ROTATION\n";
echo "Plan Start Date: " . $rot->start_date->toDateString() . "\n";
echo "User 1 Base: Morning (Step 0)\n";
echo "User 2 Base: Afternoon (Step 1)\n\n";

$testDates = [
    'Week 1' => $rot->start_date->copy(),
    'Week 2' => $rot->start_date->copy()->addWeek(),
    'Week 3' => $rot->start_date->copy()->addWeeks(2),
    'Week 4' => $rot->start_date->copy()->addWeeks(3),
];

foreach ($testDates as $label => $date) {
    echo "--- $label ({$date->toDateString()}) ---\n";
    $sh1 = UserShift::getShiftForUser($u1->id, $date->toDateString());
    $sh2 = UserShift::getShiftForUser($u2->id, $date->toDateString());
    
    echo "User 1 Shift: " . ($sh1 ? $sh1->name : 'OFF') . "\n";
    echo "User 2 Shift: " . ($sh2 ? $sh2->name : 'OFF') . "\n";
}

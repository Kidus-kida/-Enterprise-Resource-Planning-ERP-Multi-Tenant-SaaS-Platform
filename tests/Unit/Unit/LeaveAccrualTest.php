<?php

namespace Tests\Unit\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\LeaveAccrualPlan;
use App\Models\LeaveAccrualLevel;
use App\Services\Leave\AccrualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaveAccrualTest extends TestCase
{
    use RefreshDatabase;

    public function test_milestone_transition_logic()
    {
        $service = new AccrualService();

        // 1. Create a Plan
        $plan = LeaveAccrualPlan::create(['name' => 'Test Plan']);

        // 2. Create Milestones
        LeaveAccrualLevel::create([
            'leave_accrual_plan_id' => $plan->id,
            'sequence' => 1,
            'start_count' => 0,
            'start_type' => 'days',
            'accrual_amount' => 1.25,
            'accrual_frequency' => 'monthly',
        ]);

        LeaveAccrualLevel::create([
            'leave_accrual_plan_id' => $plan->id,
            'sequence' => 2,
            'start_count' => 12,
            'start_type' => 'months',
            'accrual_amount' => 1.67,
            'accrual_frequency' => 'monthly',
        ]);

        // 3. Create a User with 2 years tenure
        $user = User::factory()->create();
        EmployeeDetail::create([
            'user_id' => $user->id,
            'date_joined' => Carbon::today()->subYears(2),
        ]);

        // 4. Verify Active Level
        $activeLevel = $service->getActiveLevel($user, $plan, Carbon::today());

        $this->assertEquals(1.67, (float) $activeLevel->accrual_amount);
        $this->assertEquals(2, $activeLevel->sequence);
    }
}

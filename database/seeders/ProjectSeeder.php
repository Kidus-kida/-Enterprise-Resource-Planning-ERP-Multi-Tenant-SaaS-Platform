<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Project\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

          $client     = User::inRandomOrder()->first();
        $leader     = User::inRandomOrder()->first();
        $createdBy  = User::inRandomOrder()->first();

         Project::create([
            'name'        => 'HRM System Development',
            'client_id'   => $client?->id,
            'short_desc'  => 'Complete HR management tool with payroll and attendance.',
            'startDate'   => now()->toDateString(),
            'endDate'     => now()->addMonths(3)->toDateString(),
            'rate'        => 12000,
            'rateType'    => 'fixed', // fixed / hourly
            'priority'    => 'high',
            'leader_id'   => $leader?->id,
            'description' => 'This project includes HR modules, leave, attendance, finance, roadmap, and more.',
            'created_by'  => $createdBy?->id,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing holidays for fresh start
        Holiday::truncate();

        // =========================================
        // ETHIOPIAN HOLIDAYS (2026 - Ethiopian Calendar aligned)
        // =========================================

        // Ethiopian New Year (Enkutatash) - September 11
        Holiday::create([
            'name' => 'Ethiopian New Year (Enkutatash)',
            'startDate' => '2026-09-11',
            'endDate' => '2026-09-11',
            'description' => 'Ethiopian New Year celebration',
            'is_annual' => true,
            'color' => 'success',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Finding of the True Cross (Meskel) - September 27
        Holiday::create([
            'name' => 'Finding of the True Cross (Meskel)',
            'startDate' => '2026-09-27',
            'endDate' => '2026-09-27',
            'description' => 'Ethiopian Orthodox celebration of the Finding of the True Cross',
            'is_annual' => true,
            'color' => 'warning',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Ethiopian Christmas (Genna) - January 7
        Holiday::create([
            'name' => 'Ethiopian Christmas (Genna)',
            'startDate' => '2026-01-07',
            'endDate' => '2026-01-07',
            'description' => 'Ethiopian Orthodox Christmas celebration',
            'is_annual' => true,
            'color' => 'danger',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Ethiopian Epiphany (Timket) - January 19
        Holiday::create([
            'name' => 'Ethiopian Epiphany (Timket)',
            'startDate' => '2026-01-19',
            'endDate' => '2026-01-19',
            'description' => 'Ethiopian Orthodox Epiphany celebration',
            'is_annual' => true,
            'color' => 'info',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Victory of Adwa Day - March 2
        Holiday::create([
            'name' => 'Victory of Adwa Day',
            'startDate' => '2026-03-02',
            'endDate' => '2026-03-02',
            'description' => 'Commemoration of the Victory of Adwa',
            'is_annual' => true,
            'color' => 'success',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Ethiopian Good Friday (Variable date - using 2026 estimate)
        Holiday::create([
            'name' => 'Ethiopian Good Friday',
            'startDate' => '2026-04-24',
            'endDate' => '2026-04-24',
            'description' => 'Ethiopian Orthodox Good Friday',
            'is_annual' => true,
            'color' => 'purple',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'none',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Ethiopian Easter (Variable date - using 2026 estimate)
        Holiday::create([
            'name' => 'Ethiopian Easter (Fasika)',
            'startDate' => '2026-04-26',
            'endDate' => '2026-04-26',
            'description' => 'Ethiopian Orthodox Easter celebration',
            'is_annual' => true,
            'color' => 'warning',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // International Labour Day - May 1
        Holiday::create([
            'name' => 'International Labour Day',
            'startDate' => '2026-05-01',
            'endDate' => '2026-05-01',
            'description' => 'International Workers\' Day',
            'is_annual' => true,
            'color' => 'danger',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Patriots' Victory Day (Derg Downfall) - May 28
        Holiday::create([
            'name' => 'Patriots\' Victory Day',
            'startDate' => '2026-05-28',
            'endDate' => '2026-05-28',
            'description' => 'Commemoration of Patriots\' Victory Day',
            'is_annual' => true,
            'color' => 'success',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // =========================================
        // INTERNATIONAL/ISLAMIC HOLIDAYS
        // =========================================

        // Eid al-Fitr (Variable - estimate for 2026: April 20-21)
        Holiday::create([
            'name' => 'Eid al-Fitr',
            'startDate' => '2026-04-20',
            'endDate' => '2026-04-21',
            'description' => 'Islamic festival marking the end of Ramadan',
            'is_annual' => true,
            'color' => 'primary',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Eid al-Adha (Variable - estimate for 2026: June 27)
        Holiday::create([
            'name' => 'Eid al-Adha (Arafa)',
            'startDate' => '2026-06-27',
            'endDate' => '2026-06-28',
            'description' => 'Islamic festival of sacrifice',
            'is_annual' => true,
            'color' => 'success',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // Mawlid (Prophet's Birthday - Variable, estimate for 2026: September 5)
        Holiday::create([
            'name' => 'Mawlid (Prophet\'s Birthday)',
            'startDate' => '2026-09-05',
            'endDate' => '2026-09-05',
            'description' => 'Islamic celebration of the Prophet Muhammad\'s birthday',
            'is_annual' => true,
            'color' => 'info',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // =========================================
        // GENERAL INTERNATIONAL HOLIDAYS
        // =========================================

        // New Year's Day - January 1
        Holiday::create([
            'name' => 'New Year\'s Day',
            'startDate' => '2026-01-01',
            'endDate' => '2026-01-01',
            'description' => 'International New Year celebration',
            'is_annual' => true,
            'color' => 'primary',
            'duration' => 'full_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'next_monday',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        // International Women's Day - March 8
        Holiday::create([
            'name' => 'International Women\'s Day',
            'startDate' => '2026-03-08',
            'endDate' => '2026-03-08',
            'description' => 'Global celebration of women\'s achievements',
            'is_annual' => true,
            'color' => 'danger',
            'duration' => 'half_day',
            'applicable_to' => ['type' => 'all'],
            'exclude_from_leave' => true,
            'weekend_adjustment' => 'none',
            'is_paid' => true,
            'block_leave_requests' => false,
            'allow_attendance_exception' => false,
        ]);

        $this->command->info('✅ Public holidays seeded successfully');
        $this->command->info('   - ' . Holiday::count() . ' holidays created');
        $this->command->info('   - Ethiopian holidays: 9');
        $this->command->info('   - Islamic holidays: 3');
        $this->command->info('   - International holidays: 2');
    }
}

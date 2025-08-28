<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FwdPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = 2025;
        $workingDaysData = [];

        // Calculate working days for each month in 2025
        for ($month = 1; $month <= 12; $month++) {
            $workingDays = $this->calculateWorkingDays($year, $month);
            
            $workingDaysData[] = [
                'month' => $month,
                'year' => $year,
                'swd_amount' => $workingDays,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('fwd_period')->insert($workingDaysData);

        $this->command->info("FWD Period seeder completed for year {$year}");
        $this->command->info("Working days per month:");
        
        foreach ($workingDaysData as $data) {
            $monthName = Carbon::create($year, $data['month'], 1)->format('F');
            $this->command->info("- {$monthName} {$year}: {$data['swd_amount']} working days");
        }
    }

    private function calculateWorkingDays(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Check if current day is Monday to Friday (1-5)
            if ($currentDate->dayOfWeek >= Carbon::MONDAY && $currentDate->dayOfWeek <= Carbon::FRIDAY) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }
}

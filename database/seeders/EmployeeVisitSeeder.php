<?php

namespace Database\Seeders;

use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'area' => 'District02',
                'employee_id' => 'EMP001',
                'employee_name' => 'John Doe',
            ],
            [
                'area' => 'District02',
                'employee_id' => 'EMP002',
                'employee_name' => 'Jane Smith',
            ],
            [
                'area' => 'District03',
                'employee_id' => 'EMP003',
                'employee_name' => 'Mike Johnson',
            ],
            [
                'area' => 'District04',
                'employee_id' => 'EMP004',
                'employee_name' => 'Sarah Wilson',
            ],
        ];

        $clients = [
            'ABC Corporation', 'XYZ Ltd', 'Tech Solutions Inc', 'Global Enterprises',
            'Local Business Co', 'Retail Chain Store', 'Manufacturing Corp', 'Service Provider Ltd'
        ];

        $currentYear = date('Y');
        $currentMonth = date('n');

        foreach ($employees as $employee) {
            // Create records for last 4 months (including current month)
            for ($monthBack = 0; $monthBack < 4; $monthBack++) {
                $targetMonth = $currentMonth - $monthBack;
                $targetYear = $currentYear;
                
                // Handle year rollover
                if ($targetMonth <= 0) {
                    $targetMonth += 12;
                    $targetYear--;
                }

                $this->createEmployeeVisitRecord($employee, $targetYear, $targetMonth, $clients);
            }
        }
    }

    /**
     * Create an employee visit record with optional visit details
     */
    private function createEmployeeVisitRecord(array $employee, int $year, int $month, array $clients): void
    {
        $standardWorkingDays = $this->getStandardWorkingDays($month, $year);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Use updateOrCreate to handle potential duplicates gracefully
        $employeeVisit = EmployeeVisit::updateOrCreate(
            [
                'employee_id' => $employee['employee_id'],
                'period_year' => $year,
                'period_month' => $month,
            ],
            [
                'area' => $employee['area'],
                'employee_name' => $employee['employee_name'],
                'standard_working_days' => $standardWorkingDays,
                'total_offline_visits' => 0, // Will be calculated
                'total_online_visits' => 0,  // Will be calculated
                'adjustment_from_asm' => $this->getRandomAdjustment(),
                'note_adjustment' => $this->getRandomAdjustmentNote(),
            ]
        );

        // Determine if this should be a zero-visit month (20% chance for current month)
        $isCurrentMonth = ($year == date('Y') && $month == date('n'));
        $shouldHaveZeroVisits = $isCurrentMonth && rand(1, 100) <= 20;

        if (!$shouldHaveZeroVisits) {
            $this->createVisitDetails($employeeVisit, $year, $month, $daysInMonth, $clients);
        }

        // Recalculate totals
        $employeeVisit->recalculateTotals();
    }

    /**
     * Create visit details for an employee visit record
     */
    private function createVisitDetails(EmployeeVisit $employeeVisit, int $year, int $month, int $daysInMonth, array $clients): void
    {
        $visitDays = [];
        $numVisits = $this->getRandomVisitCount($month);
        
        for ($i = 0; $i < $numVisits; $i++) {
            $visitDay = rand(1, $daysInMonth);
            
            // Skip weekends and already visited days
            if (in_array($visitDay, $visitDays) || $this->isWeekend($year, $month, $visitDay)) {
                continue;
            }
            
            $visitDays[] = $visitDay;
            
            EmployeeVisitDetail::create([
                'employee_visit_id' => $employeeVisit->id,
                'visit_day' => $visitDay,
                'visit_type' => $this->getRandomVisitType(),
                'client_name' => $clients[array_rand($clients)],
                'visit_notes' => $this->getRandomVisitNotes(),
                'visit_datetime' => $this->createVisitDateTime($year, $month, $visitDay),
            ]);
        }
    }

    /**
     * Get standard working days for a month
     */
    private function getStandardWorkingDays(int $month, int $year): int
    {
        // Basic calculation - could be enhanced with holiday calendar
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workingDays = 0;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (!$this->isWeekend($year, $month, $day)) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }

    /**
     * Check if a date is weekend
     */
    private function isWeekend(int $year, int $month, int $day): bool
    {
        $dayOfWeek = date('N', mktime(0, 0, 0, $month, $day, $year));
        return $dayOfWeek >= 6; // Saturday = 6, Sunday = 7
    }

    /**
     * Get random adjustment value
     */
    private function getRandomAdjustment(): int
    {
        $adjustments = [-2, -1, 0, 0, 0, 1, 2, 3]; // More weight on 0
        return $adjustments[array_rand($adjustments)];
    }

    /**
     * Get random adjustment note
     */
    private function getRandomAdjustmentNote(): ?string
    {
        $notes = [
            null,
            null,
            null, // More weight on null
            'Performance adjustment',
            'Additional client meetings',
            'Holiday adjustment',
            'Training period adjustment'
        ];
        
        return $notes[array_rand($notes)];
    }

    /**
     * Get random visit count based on month
     */
    private function getRandomVisitCount(int $month): int
    {
        // Current month might have fewer visits
        $currentMonth = date('n');
        
        if ($month == $currentMonth) {
            return rand(5, 15); // Lower range for current month
        }
        
        return rand(10, 20); // Normal range for past months
    }

    /**
     * Get random visit type
     */
    private function getRandomVisitType(): string
    {
        $types = ['offline', 'offline', 'offline', 'online']; // More weight on offline
        return $types[array_rand($types)];
    }

    /**
     * Get random visit notes
     */
    private function getRandomVisitNotes(): string
    {
        $notes = [
            'Client visit for business discussion and follow-up',
            'Product presentation and demonstration',
            'Contract negotiation meeting',
            'Routine check-in and relationship building',
            'Issue resolution and support',
            'New product introduction',
            'Quarterly business review',
            'Market research and feedback collection'
        ];
        
        return $notes[array_rand($notes)];
    }

    /**
     * Create visit datetime
     */
    private function createVisitDateTime(int $year, int $month, int $day): Carbon
    {
        return Carbon::createFromDate($year, $month, $day)
            ->setTime(rand(8, 17), rand(0, 59));
    }
}
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
                'area' => 'District 01 - NORTHERN SUMATRA',
                'employee_id' => 'EMP001',
                'employee_name' => 'John Doe',
            ],
            [
                'area' => 'District 02 - SOUTHERN SUMATRA',
                'employee_id' => 'EMP002',
                'employee_name' => 'Jane Smith',
            ],
            [
                'area' => 'District 03 - WESTERN JAKARTA',
                'employee_id' => 'EMP003',
                'employee_name' => 'Mike Johnson',
            ],
            [
                'area' => 'District 04 - EASTERN JAKARTA',
                'employee_id' => 'EMP004',
                'employee_name' => 'Sarah Wilson',
            ],
            [
                'area' => 'District 05 - WEST JAVA',
                'employee_id' => 'EMP005',
                'employee_name' => 'David Lee',
            ],
            [
                'area' => 'District 06 - KALIMANTAN',
                'employee_id' => 'EMP006',
                'employee_name' => 'Emily Chen',
            ],
            [
                'area' => 'District 07 - NORTHERN CENTRAL JAVA',
                'employee_id' => 'EMP007',
                'employee_name' => 'Robert Kim',
            ],
            [
                'area' => 'District 08 - SOUTHERN CENTRAL JAVA',
                'employee_id' => 'EMP008',
                'employee_name' => 'Lisa Wang',
            ],
            [
                'area' => 'District 09 - NORTHERN EAST JAVA',
                'employee_id' => 'EMP009',
                'employee_name' => 'Thomas Nguyen',
            ],
            [
                'area' => 'District 10 - SOUTHERN EAST JAVA',
                'employee_id' => 'EMP010',
                'employee_name' => 'Maria Garcia',
            ],
            [
                'area' => 'District 11 - BALI-NUSRA',
                'employee_id' => 'EMP011',
                'employee_name' => 'James Wong',
            ],
            [
                'area' => 'District 12 - FAR EAST',
                'employee_id' => 'EMP012',
                'employee_name' => 'Anna Park',
            ],
        ];

        $clients = [
            'ABC Corporation', 'XYZ Ltd', 'Tech Solutions Inc', 'Global Enterprises',
            'Local Business Co', 'Retail Chain Store', 'Manufacturing Corp', 'Service Provider Ltd'
        ];

        $currentYear = date('Y');
        $currentMonth = date('n');

        foreach ($employees as $employee) {
            $employeeVisitRecords = [];
            
            // Create records for last 4 months (including current month)
            for ($monthBack = 0; $monthBack < 4; $monthBack++) {
                $targetMonth = $currentMonth - $monthBack;
                $targetYear = $currentYear;
                
                // Handle year rollover
                if ($targetMonth <= 0) {
                    $targetMonth += 12;
                    $targetYear--;
                }

                $employeeVisitRecords[] = $this->createEmployeeVisitRecord($employee, $targetYear, $targetMonth, $clients);
            }
            
            // Ensure minimum grand total of 16 visits across all months
            $this->ensureMinimumGrandTotal($employeeVisitRecords, $clients);
        }
    }

    /**
     * Ensure the grand total of visits across all months is at least 19
     */
    private function ensureMinimumGrandTotal(array $employeeVisitRecords, array $clients): void
    {
        $grandTotal = 0;
        
        // Calculate current grand total
        foreach ($employeeVisitRecords as $record) {
            $record->recalculateTotals();
            $grandTotal += $record->total_offline_visits + $record->total_online_visits;
        }
        
        // If grand total is less than 16, add visits to reach minimum
        if ($grandTotal < 16) {
            $visitsNeeded = 16 - $grandTotal;
            
            // Distribute additional visits across the records
            $recordIndex = 0;
            while ($visitsNeeded > 0) {
                $record = $employeeVisitRecords[$recordIndex % count($employeeVisitRecords)];
                
                // Add one more visit to this record
                $this->addAdditionalVisit($record, $clients);
                $visitsNeeded--;
                $recordIndex++;
            }
        }
    }

    /**
     * Add an additional visit to an employee visit record
     */
    private function addAdditionalVisit(EmployeeVisit $employeeVisit, array $clients): void
    {
        $year = $employeeVisit->period_year;
        $month = $employeeVisit->period_month;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // Get existing visit days to avoid duplicates
        $existingVisitDays = $employeeVisit->visitDetails()->pluck('visit_day')->toArray();
        
        // Find an available day for the visit
        $attempts = 0;
        do {
            $visitDay = rand(1, $daysInMonth);
            $attempts++;
        } while (
            (in_array($visitDay, $existingVisitDays) || $this->isWeekend($year, $month, $visitDay)) 
            && $attempts < 50
        );
        
        // If we couldn't find a weekday, use any available day
        if ($attempts >= 50) {
            do {
                $visitDay = rand(1, $daysInMonth);
                $attempts++;
            } while (in_array($visitDay, $existingVisitDays) && $attempts < 100);
        }
        
        EmployeeVisitDetail::create([
            'employee_visit_id' => $employeeVisit->id,
            'visit_day' => $visitDay,
            'visit_type' => $this->getRandomVisitType(),
            'client_name' => $clients[array_rand($clients)],
            'visit_notes' => $this->getRandomVisitNotes(),
            'visit_datetime' => $this->createVisitDateTime($year, $month, $visitDay),
        ]);
        
        // Recalculate totals after adding the visit
        $employeeVisit->recalculateTotals();
    }

    /**
     * Create an employee visit record with optional visit details
     */
    private function createEmployeeVisitRecord(array $employee, int $year, int $month, array $clients): EmployeeVisit
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

        // Determine if this should be a zero-visit month (reduced chance to 5% to help meet minimum total)
        $isCurrentMonth = ($year == date('Y') && $month == date('n'));
        $shouldHaveZeroVisits = $isCurrentMonth && rand(1, 100) <= 5;

        if (!$shouldHaveZeroVisits) {
            $this->createVisitDetails($employeeVisit, $year, $month, $daysInMonth, $clients);
        }

        // Recalculate totals
        $employeeVisit->recalculateTotals();
        
        return $employeeVisit;
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
            return rand(3, 8); // Lower range for current month but ensuring some minimum
        }
        
        return rand(4, 12); // Adjusted range to help meet minimum total requirement
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
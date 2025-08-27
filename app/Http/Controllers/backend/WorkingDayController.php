<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitDetail;
use App\Models\FwdDistrictArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingDayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display the actual working day view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $districtAreas = FwdDistrictArea::getAreasForSelect();
        return view('backend.workingday.actual_working_day', compact('districtAreas'));
    }

    /**
     * Get actual working day data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            // Get parameters from request
            $year = $request->input('year') ?: date('Y');
            $month = $request->input('month') ?: date('m');
            $area = $request->input('area');
            $virtual = $request->input('virtual', 'No') === 'Yes' ? "'Yes'" : "'No'";
            
            // Build area filter for SQL
            $areaFilter = '';
            if ($area) {
                $areaFilter = "AND sales_hierarchy LIKE '%{$area}%'";
            }
            
            // Raw SQL query based on the provided structure
            $sql = "
                SELECT *, 
                       CONCAT('District ', ma.district_seq, ' - ', ma.area_name) as District
                FROM (
                    SELECT * FROM (
                        SELECT
                            Year, Month, Day, employee_name, 
                            MAX(Count) as Act_Count, sales_hierarchy
                        FROM (
                            SELECT 
                                YEAR(start_time) AS Year, 
                                MONTH(start_time) AS Month,
                                DAY(start_time) AS Day, 
                                employee_name, 
                                1 as Count,
                                sales_hierarchy
                            FROM fwd_crm_visits
                            WHERE status = 'Completed' 
                                AND visit_type = 'Account Visit' 
                                AND is_phone_call = {$virtual}
                        ) activities
                        WHERE Year = {$year} 
                            AND Month = {$month} 
                            {$areaFilter}
                        GROUP BY Year, Month, employee_name, Day, sales_hierarchy
                    ) a
                    
                    UNION ALL
                    
                    SELECT {$year}, {$month}, yy.Day, '', 1, ''
                    FROM (
                        SELECT DAY(full_date) as Day
                        FROM dim_period
                        WHERE year_num = {$year} AND month_num = {$month}
                    ) yy
                ) as wadaw
                LEFT JOIN fwd_district_areas ma ON SUBSTRING(sales_hierarchy, 10, 2) = ma.area_code
                WHERE employee_name <> '' AND sales_hierarchy <> ''
                ORDER BY employee_name, Day
            ";
            
            $rawData = DB::select($sql);
            
            if (empty($rawData)) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'No data found'
                ]);
            }
            
            // Group data by employee and transform for grid
            $groupedData = [];
            foreach ($rawData as $row) {
                $key = $row->employee_name . '_' . $row->sales_hierarchy;
                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'employee_name' => $row->employee_name,
                        'sales_hierarchy' => $row->sales_hierarchy,
                        'district' => $row->District ?? '',
                        'year' => $row->Year,
                        'month' => $row->Month,
                        'days' => []
                    ];
                }
                if ($row->Day) {
                    $groupedData[$key]['days'][$row->Day] = $row->Act_Count ?? 0;
                }
            }
            
            // Transform to final format
            $data = [];
            foreach ($groupedData as $employee) {
                $record = [
                    'employee_name' => $employee['employee_name'],
                    'area' => $employee['district'],
                    'sales_hierarchy' => $employee['sales_hierarchy'],
                    'year' => $employee['year'],
                    'month' => $employee['month'],
                    'standard_working_days' => 19, // Default value
                    'total_offline_visits' => 0,
                    'total_online_visits' => 0,
                    'asm_adjustment' => 0,
                    'note' => '',
                    'final_total_visits' => array_sum($employee['days'])
                ];
                
                // Add day columns
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $employee['month'], $employee['year']);
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $record["day_$i"] = $employee['days'][$i] ?? 0;
                }
                
                $data[] = $record;
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAdjustment(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'id' => 'required|integer',
                'adjustment_value' => 'required|numeric',
                'note' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $visit = EmployeeVisit::find($request->id);
            
            if (!$visit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee visit record not found'
                ], 404);
            }

            $visit->adjustment_from_asm = $request->adjustment_value;
            $visit->note_adjustment = $request->note ?? 'Adjustment made by ASM';
            $visit->standard_working_days = $request->working_days + $request->adjustment_value;
            $visit->final_total_visits = $visit->total_offline_visits + $visit->total_online_visits + $request->adjustment_value;
            $visit->save();

            return response()->json([
                'success' => true,
                'message' => 'Adjustment updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate the next unique transaction number for working day (format: TRyy-00001)
     */
    public function generateTransNo()
    {
        $year = date('y');
        $prefix = 'TR' . $year . '-';
        
        // Find the highest transNo for this year from fwd_hdr table
        $last = DB::table('fwd_hdr')
            ->where('transNo', 'like', $prefix . '%')
            ->orderByDesc('transNo')
            ->value('transNo');
            
        $nextNumber = 1;
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5})$/', $last, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }
        
        $nextTransNo = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return response()->json(['trans_no' => $nextTransNo]);
    }

}
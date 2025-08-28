<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitDetail;
use App\Models\FwdDistrictArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            $areas = $request->input('area', []);
            $virtual = $request->input('virtual', 'No') == 'Yes' ? 1 : 0;
            
            $standardWorkingDays = DB::table('fwd_period')
                ->where('year', $year)
                ->where('month', $month)
                ->value('swd_amount');
            
            // Fallback to default value if not found
            if (!$standardWorkingDays) {
                $standardWorkingDays = 19;
            }
            
            // Build area filter for SQL
            $areaFilter = '';
            if (!empty($areas) && is_array($areas)) {
                $areaConditions = [];
                foreach ($areas as $area) {
                    $areaConditions[] = "sales_hierarchy LIKE '%{$area}%'";
                }
                $areaFilter = "AND (" . implode(' OR ', $areaConditions) . ")";
            }
            
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
                    'standard_working_days' => $standardWorkingDays,
                    'total_offline_visits' => 0,
                    'total_online_visits' => 0,
                    'asm_adjustment' => 0,
                    'note' => '',
                    'other_days' => 3,
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
            $validator = Validator::make($request->all(), [
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
     * Store new working day data
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transNo' => 'required|string|max:50',
                'transDate' => 'required|date',
                'period' => 'required|date',
                'area' => 'nullable|array',
                'remark' => 'nullable|string|max:255',
                'gridData' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Convert area array to string if needed
            $areaValue = null;
            if ($request->area && is_array($request->area)) {
                $areaValues = [];
                foreach ($request->area as $area) {
                    if (is_array($area) || is_object($area)) {
                        $areaValues[] = $area['value'] ?? $area->value ?? '';
                    } else {
                        $areaValues[] = $area;
                    }
                }
                $areaValue = implode(',', array_filter($areaValues));
            } elseif ($request->area) {
                $areaValue = $request->area;
            }

            DB::table('fwd_hdr')->insert([
                'transNo' => $request->transNo,
                'transDate' => Carbon::parse($request->transDate)->format('Y-m-d H:i:s'),
                'period' => Carbon::parse($request->period)->format('Y-m-d'),
                'area' => $areaValue,
                'remark' => $request->remark,
                'status_record_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Prepare detail records for batch insert
            $detailRecords = [];
            foreach ($request->gridData as $row) {
                if (!empty($row['employee_name'])) {
                    $detailRecords[] = [
                        'transNo' => $request->transNo,
                        'empName' => $row['employee_name'],
                        'adjustment' => $row['asm_adjustment'] ?? 0,
                        'notes' => $row['note'] ?? '',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }

            if (!empty($detailRecords)) {
                $chunks = array_chunk($detailRecords, 20);
                foreach ($chunks as $chunk) {
                    DB::table('fwd_dtl')->insert($chunk);
                }
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Working day data saved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save working day data: ' . $e->getMessage()
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

    /**
     * Get combined list of fwd_hdr with fwd_dtl records
     */
    public function getFwdList($transNo = null)
    {
        try {
            if ($transNo) {
                // Get specific transaction with working day details
                $headerData = DB::table('fwd_hdr')
                    ->where('transNo', $transNo)
                    ->first();

                if (!$headerData) {
                    return response()->json([
                        'success' => false,
                        'data' => [],
                        'message' => 'Transaction not found'
                    ], 404);
                }

                // Get working day data using the same SQL as getData method
                $year = Carbon::parse($headerData->period)->year;
                $month = Carbon::parse($headerData->period)->month;
                $area = $headerData->area;
                $virtual = 0; // Default to offline visits
                
                $standardWorkingDays = DB::table('fwd_period')
                    ->where('year', $year)
                    ->where('month', $month)
                    ->value('swd_amount');
                
                if (!$standardWorkingDays) {
                    $standardWorkingDays = 19;
                }
                
                // Build area filter for SQL
                $areaFilter = '';
                if ($area) {
                    $areaFilter = "AND sales_hierarchy LIKE '%{$area}%'";
                }
                
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
                        'data' => [
                            'header' => $headerData,
                            'details' => []
                        ],
                        'message' => 'No working day data found'
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
                
                // Get existing adjustments from fwd_dtl
                $adjustments = DB::table('fwd_dtl')
                    ->where('transNo', $transNo)
                    ->get()
                    ->keyBy('empName');
                
                // Transform to final format
                $workingDayData = [];
                foreach ($groupedData as $employee) {
                    $empName = $employee['employee_name'];
                    $adjustment = $adjustments->get($empName);
                    
                    $record = [
                        'employee_name' => $empName,
                        'area' => $employee['district'],
                        'sales_hierarchy' => $employee['sales_hierarchy'],
                        'employee_id' => '',
                        'year' => $employee['year'],
                        'month' => $employee['month'],
                        'standard_working_days' => $standardWorkingDays,
                        'total_offline_visits' => 0,
                        'total_online_visits' => 0,
                        'asm_adjustment' => $adjustment ? ($adjustment->adjustment ?? 0) : 0,
                        'note' => $adjustment ? ($adjustment->notes ?? '') : '',
                        'other_days' => 3,
                        'final_total_visits' => array_sum($employee['days'])
                    ];
                    
                    // Add day columns
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $employee['month'], $employee['year']);
                    for ($i = 1; $i <= $daysInMonth; $i++) {
                        $record["day_$i"] = $employee['days'][$i] ?? 0;
                    }
                    
                    $workingDayData[] = $record;
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'header' => $headerData,
                        'details' => $workingDayData
                    ],
                    'message' => 'Data retrieved successfully'
                ]);

            } else {
                // Get all headers with detail count
                $data = DB::table('fwd_hdr as h')
                    ->leftJoin('fwd_dtl as d', 'h.transNo', '=', 'd.transNo')
                    ->select(
                        'h.id',
                        'h.transNo',
                        'h.transDate',
                        'h.period',
                        'h.area',
                        'h.remark',
                        'h.status_record_id',
                        'h.created_at',
                        'h.updated_at',
                        DB::raw('COUNT(d.id) as detail_count')
                    )
                    ->groupBy('h.id', 'h.transNo', 'h.transDate', 'h.period', 'h.area', 'h.remark', 'h.status_record_id', 'h.created_at', 'h.updated_at')
                    ->orderByDesc('h.created_at')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'message' => 'Data retrieved successfully'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing working day data
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transNo' => 'required|string|max:50',
                'transDate' => 'required|date',
                'period' => 'required|date',
                'area' => 'nullable|array',
                'remark' => 'nullable|string|max:255',
                'gridData' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Convert area array to string if needed
            $areaValue = null;
            if ($request->area && is_array($request->area)) {
                $areaValues = [];
                foreach ($request->area as $area) {
                    if (is_array($area) || is_object($area)) {
                        $areaValues[] = $area['value'] ?? $area->value ?? '';
                    } else {
                        $areaValues[] = $area;
                    }
                }
                $areaValue = implode(',', array_filter($areaValues));
            } elseif ($request->area) {
                $areaValue = $request->area;
            }

            // Update header record
            DB::table('fwd_hdr')
                ->where('transNo', $request->transNo)
                ->update([
                    'transDate' => Carbon::parse($request->transDate)->format('Y-m-d H:i:s'),
                    'period' => Carbon::parse($request->period)->format('Y-m-d'),
                    'area' => $areaValue,
                    'remark' => $request->remark,
                    'updated_at' => Carbon::now()
                ]);

            // Delete existing detail records
            DB::table('fwd_dtl')->where('transNo', $request->transNo)->delete();

            // Prepare new detail records for batch insert
            $detailRecords = [];
            foreach ($request->gridData as $row) {
                if (!empty($row['employee_name'])) {
                    $detailRecords[] = [
                        'transNo' => $request->transNo,
                        'empName' => $row['employee_name'],
                        'adjustment' => $row['asm_adjustment'] ?? 0,
                        'notes' => $row['note'] ?? '',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
            }

            // Insert detail records in chunks
            if (!empty($detailRecords)) {
                $chunks = array_chunk($detailRecords, 50);
                foreach ($chunks as $chunk) {
                    DB::table('fwd_dtl')->insert($chunk);
                }
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Working day data updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update working day data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post working day data (update status to posted)
     */
    public function posted(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transNo' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if record exists
            $record = DB::table('fwd_hdr')->where('transNo', $request->transNo)->first();
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Update status to posted (2)
            DB::table('fwd_hdr')
                ->where('transNo', $request->transNo)
                ->update([
                    'status_record_id' => 2,
                    'updated_at' => Carbon::now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Working day data posted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to post working day data: ' . $e->getMessage()
            ], 500);
        }
    }

}
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitDetail;
use Illuminate\Http\Request;

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
        return view('backend.workingday.actual_working_day');
    }

    /**
     * Get actual working day data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Get parameters from request
        $year = $request->input('year') ?: date('Y');
        $month = $request->input('month') ?: date('m');
        $area = $request->input('area');
        
        // Query employee visits by period
        $query = EmployeeVisit::byPeriod($year, $month);
        
        if ($area) {
            $query->where('area', 'like', '%' . $area . '%');
        }
        // Get employee visits with their details
        $employeeVisits = $query->with('visitDetails')->get();
        
        // Transform the data to match the expected format
        $data = $employeeVisits->map(function ($visit) {
            // Calculate days in month for the period
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $visit->period_month, $visit->period_year);
            
            // Get the visit days data
            $visitDays = $visit->getVisitsByDayArray();
            
            $record = [
                'id' => $visit->id,
                'employee_id' => $visit->employee_id,
                'employee_name' => $visit->employee_name,
                'year' => $visit->period_year,
                'month' => $visit->period_month,
                'area' => $visit->area,
                'standard_working_days' => $visit->standard_working_days,
                'total_offline_visits' => $visit->total_offline_visits,
                'total_online_visits' => $visit->total_online_visits,
                'asm_adjustment' => $visit->adjustment_from_asm,
                'note' => $visit->note_adjustment,
                'final_total_visits' => $visit->final_total_visits
            ];
            
            // Add visit days data (day_1, day_2, etc.)
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $record["day_$i"] = $visitDays[$i] ?? 0;
            }
            
            return $record;
        });

        if (!$data->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data retrieved successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'No data found'
        ]);
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

}
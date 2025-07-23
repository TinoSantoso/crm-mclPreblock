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
        
        // Apply area filter if provided
        if ($area) {
            $query->byArea($area);
        }
        
        // Get employee visits with their details
        $employeeVisits = $query->with('visitDetails')->get();
        
        // Transform the data to match the expected format
        $data = $employeeVisits->map(function ($visit) {
            // Calculate days in month for the period
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $visit->period_month, $visit->period_year);
            
            // Get the visit days data
            $visitDays = $visit->getVisitsByDayArray();
            
            // Create the base record
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
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data retrieved successfully'
        ]);
    }
}